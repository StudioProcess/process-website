<?php
include 'sync_pw.php'; // credentials

class PrcsSync extends PrcsCredentials {
   private static $debug = false;

   const TABLE_OPTIONS = 'sync_options';
   const TABLE_INSTAGRAM = 'sync_instagram_posts';
   const TABLE_TWITTER = 'sync_twitter_posts';

   const IG_URL_OLD = 'https://api.instagram.com/v1/users/2227354795/media/recent';
   const TWI_URL = 'https://api.twitter.com/1.1/statuses/user_timeline.json?user_id=3439338467';
   
   const IG_URL = 'https://graph.instagram.com/17841402243858419/media';
   const IG_AUTH_URL = 'https://api.instagram.com/oauth/authorize';
   const IG_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';
   
   const TAG = 'studioprocess';
   
   const DOWNLOAD_IG_MEDIA = true;
   const IG_MEDIA_DIR = "wp-content/ig_media";


   /**
    * DATABASE
    */
   // connect to the db
   private static function db_connect() {
      return new mysqli( self::SQL_SERVER, self::SQL_USER, self::SQL_PASS, self::SQL_DB );
   }

   // disconnect from db
   private static function db_disconnect($db) {
      return $db->close();
   }

   // print errors
   private static function db_errors($db) {
      if (!empty($db->error)) self::debug($db->error);
   }

   // process results of a multi_query
   private static function db_process_results($db) {
      self::db_errors($db);
      do {
         /* store first result set */
         if ( $result = $db->store_result() ) {
            $result.free();
         } else {
            self::db_errors($db);
         }
      } while ( $db->more_results() && $db->next_result() );
   }

   // create the necessary tables if they don't exist
   private static function db_init($db) {
      $query  = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_OPTIONS . '(id VARCHAR(255), content TEXT, UNIQUE (id));';
      $query .= 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_INSTAGRAM . '(id VARCHAR(255), timestamp INT, content TEXT, UNIQUE (id));';
      $query .= 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_TWITTER . '(id VARCHAR(255), timestamp INT, content TEXT, UNIQUE (id));';
      $db->multi_query($query);
      self::db_process_results($db, true);
   }

   // generic sync function
   private static function db_sync($db, $posts, $table, $option) {
      self::debug("$table: " . count($posts) . " posts found");
      
      if ( empty($posts) || sizeof($posts) == 0) return; // no new posts

      $ids = array(); // returned ids, quoted etc.
      foreach ($posts as $post) {
         $ids[] = sprintf( "('%s')", $post->id );
      }
      self::debug($ids);
      // $min_id = end($posts)->id;
      // $max_id = $posts[0]->id;


      $query = "BEGIN;" . PHP_EOL; // begin transaction
      // delete posts that are in db but missing from new data
      // TODO : need to sync back a certain time so i have a real min id i can start from looking for deletions
      // $query .= sprintf( "DELETE FROM %s WHERE id > '%s' AND id NOT IN (%s);" . PHP_EOL, $table, $min_id, join(',', $ids) );
      $query .= sprintf( 'DELETE FROM %s WHERE id NOT IN (%s);' . PHP_EOL, $table, join(',', $ids) );
      // add or update all posts and track new last_id
      $insert = 'INSERT INTO %s VALUES (\'%s\', %d, \'%s\') ON DUPLICATE KEY UPDATE id=\'%2$s\', timestamp=%3$d, content=\'%4$s\'; ' . PHP_EOL;
      $last_id = '';
      foreach($posts as $post) {
         if ( $post->id > $last_id ) $last_id = $post->id; // use highest id
         $query .= sprintf( $insert, $table, $post->id, $post->timestamp, $db->real_escape_string($post->content) );
      };
      if ( !empty($last_id) ) { // also update last synced id
         $query .= self::db_set_option_sql($option, $last_id);
      }
      $query .= "COMMIT;"; // end transaction

      self::debug($query);
      $db->multi_query($query);
      self::db_process_results($db, true);
   }

   // get an option from db (empty string if not found)
   private static function db_get_option($db, $option) {
      $query = sprintf("SELECT content FROM %s WHERE id='%s';", self::TABLE_OPTIONS, $option);
      $result = $db->query($query);
      self::db_errors($db);
      $value= '';
      if ($result) {
         // self::debug($result);
         if ($result->num_rows > 0) {
            $value = $result->fetch_object()->content;
         }
         $result->free();
      }
      return $value;
   }

   // get sql for setting an option in the db
   private static function db_set_option_sql($option, $value) {
      return sprintf("INSERT INTO %s VALUES ('%s', '%s') ON DUPLICATE KEY UPDATE content = '%3\$s';" . PHP_EOL, self::TABLE_OPTIONS, $option, $value);
   }

   // query post content from db (returns json)
   private static function db_get_posts($db, $table, $count=0, $min_time=0, $max_id='') {
      $query = 'SELECT * FROM ' . $table;
      if ($min_time > 0) {
         $query .= sprintf(' WHERE timestamp > %d', $min_time);
      }
      if (!empty($max_id)) {
         if ($min_time > 0) $query .= ' AND ';
         else $query .= ' WHERE ';
         $query .= sprintf("id < '%s'", $max_id);
      }
      $query .= ' ORDER BY timestamp DESC';
      if ($count > 0) {
         $query .= ' LIMIT ' . $count;
      }

      $posts = array();
      $result = $db->query($query);
      if ($result) {
         while ($row = $result->fetch_object()) {
            // self::twi_post_decode($row->content);
            // self::debug(self::twi_post_decode($row->content));
            array_push( $posts, $row->content );
         }
         $result->free();
      }
      return $posts;
   }



   /**
    * UTIL
    */

   private static function to_timestamp($str) {
      $date = new DateTime($str);
      return $date->getTimestamp();
   }

    // debug print something
    private static function debug($thing, $force=false) {
      if (!self::$debug && !$force) return;
      echo '<pre>';
      print_r($thing);
      echo '</pre>';
    }

   // perform a get request with curl
   private static function curl_get( $method, $url, $headers=array(), $post_fields='' ) {
       $options = array(
           CURLOPT_RETURNTRANSFER => true,     // return web page
           CURLOPT_HEADER         => false,    // don't return headers
           // CURLOPT_FOLLOWLOCATION => true,     // follow redirects
           CURLOPT_ENCODING       => "",       // handle all encodings
           CURLOPT_USERAGENT      => "spider", // who am i
           CURLOPT_AUTOREFERER    => true,     // set referer on redirect
           CURLOPT_CONNECTTIMEOUT => 3,      // timeout on connect
           CURLOPT_TIMEOUT        => 30,      // timeout on response
           CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
           CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
       );

       if ($method == 'POST') {
          $options[CURLOPT_POST] = true;
       } else {
          $options[CURLOPT_HTTPGET] = true;
       }

       if (!empty($headers)) {
          $options[CURLOPT_HTTPHEADER] = $headers;
       }

       if (!empty($post_fields)) {
          $options[CURLOPT_POSTFIELDS] = $post_fields;
       }

       $ch      = curl_init( $url );
       curl_setopt_array( $ch, $options );
       $content = curl_exec( $ch );
       $err     = curl_errno( $ch );
       $errmsg  = curl_error( $ch );
       $header  = curl_getinfo( $ch );
       curl_close( $ch );

       $header['errno']   = $err;
       $header['errmsg']  = $errmsg;
       $header['content'] = $content;
       return $header;
   }
   
   // returns true on successful download and save
   // otherwise returns false
   private static function curl_download( $url, $filename ) {
      $filename = $_SERVER["DOCUMENT_ROOT"] . "/" . $filename;
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
      $raw = curl_exec($ch);
      if ($raw === false) return false;
      curl_close($ch);
      if (file_exists($filename)) {
         unlink($filename);
      }
      $fp = fopen($filename, 'x');
      if ($fp === false) return false;
      $res = fwrite($fp, $raw);
      if ($res === false) return false;
      $res = fclose($fp);
      if ($res === false) return false;
      return true;
   }
   
   // returns true if folder was created
   private static function ensure_folder_exists($folder) {
      $folder = $_SERVER["DOCUMENT_ROOT"] . "/" . $folder;
      if ( !file_exists($folder) ) {
         return mkdir($folder, 0755, true);
      }
      return false;
   }
   
   // removes the hash tag from a string
   private static function remove_tag($str) {
      $pattern = '|\s*#'. self::TAG .'\s*|i';
      return preg_replace($pattern, ' ', $str);
   }



   /**
    * TWITTER
    */
   // build credentials string
   private static function twi_credentials() {
      return base64_encode( urlencode(self::TWI_KEY) . ':' . urlencode(self::TWI_SECRET) );
   }
   // request a bearer token
   private static function twi_get_token() {
      $headers = array(
         'Authorization: Basic ' . self::twi_credentials(),
         'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
      );
      $post_data = 'grant_type=client_credentials';
      $response = self::curl_get('POST', 'https://api.twitter.com/oauth2/token', $headers, $post_data);
      if ($response['http_code'] != 200) return '';
      return json_decode($response['content'])->access_token;
   }

   // get twitter data (json)
   // empty array on failure
   private static function twi_query($count=0, $min_id='', $max_id='') {
      $headers = array(
         'Authorization: Bearer ' . self::twi_get_token()
      );
      $url = self::TWI_URL . '&trim_user=1&exclude_replies=1';
      if ($count > 0) $url .= '&count=' . $count;
      if (!empty($min_id)) $url .= '&since_id=' . $min_id;
      if (!empty($max_id)) $url .= '&max_id=' . $max_id;
      $response = self::curl_get('GET', $url, $headers);
      if ($response['http_code'] != 200) return array();
      return json_decode( $response['content'] );
   }

   // sync twitter data to db
   private static function twi_sync($db) {
      // $last_id = self::db_get_option($db, 'twi_last_id'); // get id of last synced post
      // self::debug($last_id);
      //
      $response = self::twi_query(); // get twitter data
      if ( empty($response) ) return;
      $posts = array();
      foreach ($response as $post) {
         if ( self::twi_is_tagged($post) ) {
            $posts[] = self::twi_format_for_db($post);
         }
      }
      return self::db_sync($db, $posts, self::TABLE_TWITTER, 'twi_last_id');
   }

   // check whether a twitter post is tagged
   private static function twi_is_tagged($post) {
      foreach ($post->entities->hashtags as $tag) {
         if ( strtolower($tag->text) == strtolower(self::TAG) ) return true;
      }
      return false;
   }

   // format twitter data for syncing to the database (extract id and timestamp)
   private static function twi_format_for_db($post) {
      return (object)array(
         'id' => $post->id,
         'timestamp' => self::to_timestamp($post->created_at),
         'content' => json_encode($post)
      );
   }

   // format twitter data for wordpress. extract relevant properties
   private static function twi_format_for_wp($post_json) {
      $post = json_decode($post_json);
      return (object)array(
         'id' => $post->id,
         'service' => 'twitter',
         'timestamp' => self::to_timestamp($post->created_at),
         'text' => self::remove_tag( $post->text ),
         // 'image' => $post->images->standard_resolution,
         'link' => sprintf('https://twitter.com/%s/status/%s/', $post->user->id, $post->id)
      );
   }


   /**
    * INSTAGRAM
    */
    
    // check for ig auth code (from url redirect)
    private static function ig_authcode() {
      if ( isset($_GET['code']) ) return $_GET['code'];
      return NULL;
    }
    
    // performs authentication to instagram basic api
    // if called without authcode (code query parameter) redirects to instagrm oauth login
    // returns an object with 'access_token' and 'user_id' fields
    private static function ig_auth() {
      $code = self::ig_authcode();
      
      if (!$code) {
        // get auth code (via redirect)
        $url = self::IG_AUTH_URL . '?client_id=' . self::IG_APP_ID . '&redirect_uri=' . self::IG_REDIRECT_URI . '&scope=user_profile,user_media&response_type=code';
        $response = self::curl_get('GET', $url);
        if ($response['http_code'] == 301 || $response['http_code'] == 302) {
          header("Location: " . $response['url']);
          return;
        }
        self::debug($response);
        return;
      }
      // get access token (using auth code)
      $post_data = "client_id=" . self::IG_APP_ID . "&client_secret=" . self::IG_APP_SECRET . "&grant_type=authorization_code&redirect_uri=" . self::IG_REDIRECT_URI . "&code=" . $code;
      $response = self::curl_get('POST', self::IG_TOKEN_URL, array(), $post_data);
      
      if ($response['http_code'] == 400) {
        //"error_message": "This authorization code has been used"
        $path = strtok($_SERVER["REQUEST_URI"], '?'); // path without query string
        header("Location: https://$_SERVER[HTTP_HOST]$path");
        return;
      }
      
      if ($response['http_code'] == 200) {
        return json_decode( $response['content'] );
      }
      self::debug($response);
    }

   
   // get instagram data (json) empty array on failure
   private static function ig_query($auth) {
      $url = self::IG_URL . '?access_token=' . $auth->access_token . '&fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp';
      $response = self::curl_get('GET', $url); // instagram response
      if ($response['http_code'] != 200) {
        self::debug($response);
        return array();
      }
      return json_decode( $response['content'] );
   }
   
   // returns an array mapping post id to saved file urls
   private static function ig_download_media($post_json) {
      $out = array();
      self::ensure_folder_exists(self::IG_MEDIA_DIR);
      foreach ($post_json as $post) {
         if ( self::ig_is_tagged($post) ) {
            $ext = pathinfo(parse_url($post->media_url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = self::IG_MEDIA_DIR . "/" . $post->id . "." . $ext;
            $res = self::curl_download( $post->media_url, $filename );
            if ($res) $out[$post->id] = $filename;
         }
      }
      return $out;
   }
   
   // sync instagram data to db
   private static function ig_sync($db, $auth) {
      $response = self::ig_query($auth); // get instagram data
      $posts = array();
      if ( empty($response) ) return;
      
      $downloaded_media = array();
      if (self::DOWNLOAD_IG_MEDIA) {
         $downloaded_media = self::ig_download_media($response->data);
         self::debug($downloaded_media);
      }
      
      foreach ($response->data as $post) {
         if ( self::ig_is_tagged($post) ) {
            if (array_key_exists($post->id, $downloaded_media)) {
               $post = (array)$post;
               $post['_downloaded_media_url'] = $downloaded_media[$post['id']];
               $post = (object)$post;
            }
            $posts[] = self::ig_format_for_db($post);
         }
      }
      // print_r($posts);
      return self::db_sync($db, $posts, self::TABLE_INSTAGRAM, 'ig_last_id');
   }

   // check wheter an instagram post is tagged
   private static function ig_is_tagged($post) {
     return strpos( strtolower($post->caption), '#' . strtolower(self::TAG) ); // returns false if not found
   }
   
   // format instagram data for syncing to the database (extract id and timestamp)
   private static function ig_format_for_db($post) {
      return (object)array(
         'id' => $post->id,
         'timestamp' => strtotime($post->timestamp), # iso time string to unix timestamp
         'content' => json_encode($post)
      );
   }
   
   // format instagram post (legacy)
   private static function ig_format_for_wp_legacy($post) {
      return (object)array(
         'id' => $post->id,
         'service' => 'instagram',
         'version' => 'legacy', // version info
         'timestamp' => $post->created_time,
         'text' => self::remove_tag( $post->caption->text ),
         'type' => strtoupper($post->type), // IMAGE, VIDEO
         'image' => $post->images->standard_resolution->url,
         'video' => $post->videos ? $post->videos->standard_resolution->url : "",
         'link' => $post->link
      );
   }

   // format instagram post json and extrac relevant properties
   private static function ig_format_for_wp($post_json) {
      $post = json_decode($post_json);
      if (property_exists($post, 'created_time')) return self::ig_format_for_wp_legacy($post); # created_time is only on legacy api data
         
      $media_url = $post->media_url;
      if (self::DOWNLOAD_IG_MEDIA && property_exists($post, "_downloaded_media_url")) {
         $media_url = $post->_downloaded_media_url;
      }
      return (object)array(
         'id' => $post->id,
         'service' => 'instagram',
         'timestamp' => strtotime($post->timestamp), // unix timestamp
         'text' => self::remove_tag( $post->caption ),
         'type' => $post->media_type, // IMAGE, CAROUSEL_ALBUM, VIDEO
         'image' => $post->media_type == 'VIDEO' ? $post->thumbnail_url : $media_url,
         'video' => $post->media_type == 'VIDEO' ? $media_url : "", // video url or empty string
         'link' => $post->permalink
      );
   }


   /**
    * PUBLIC API
    */

    // enable/disable debug output
    public static function set_debug($bool) {
      self::$debug = $bool;
    }

   // get new posts from instagram and twitter and store in db
   public static function sync() {
      $auth = self::ig_auth();
      $db = self::db_connect();
      self::ig_sync($db, $auth);
      self::twi_sync($db);
      self::db_disconnect($db);
   }
   
   public static function get_instagram_posts($count=0, $min_time=0, $max_id='') {
      $db = self::db_connect();
      $posts_content = self::db_get_posts($db, self::TABLE_INSTAGRAM, $count, $min_time, $max_id);
      self::db_disconnect($db);
      $posts = array();
      foreach ($posts_content as $post_content) {
         $posts[] = self::ig_format_for_wp($post_content) ;
      }
      return $posts;
   }

   // get twitter posts
   public static function get_twitter_posts($count=0, $min_time=0, $max_id='') {
      $db = self::db_connect();
      self::db_errors($db);
      $posts_content = self::db_get_posts($db, self::TABLE_TWITTER, $count, $min_time, $max_id);
      self::db_disconnect($db);
      $posts = array();
      foreach ($posts_content as $post_content) {
         $posts[] = self::twi_format_for_wp($post_content) ;
      }
      return $posts;
   }


   public static function test() {
      // $db = self::db_connect();
      // self::twi_sync($db);
      // self::db_disconnect($db);

      // self::debug( self::get_instagram_posts() );
      // self::debug( self::get_instagram_posts() );

      // $ig = self::ig_query();
      // print_r($ig);

      // self::sync();
      
      // $res = self::ig_query($auth);
      // print_r($res);
      
      // self::ig_sync(NULL, $auth);
      
      $auth = self::ig_auth();
      // print_r($auth);
      $db = self::db_connect();
      $res_ig = self::ig_sync($db, $auth);
      // print_r($res_ig);
      // self::twi_sync($db);
      self::db_disconnect($db);
   }

}


   // PrcsSync::test( );

?>
