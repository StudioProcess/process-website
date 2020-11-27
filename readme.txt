Process Website - Wordpress Theme

How to build in 2020:
* Use node v8.17.0
* `npm install`

How to run:
* Have proper FTP credentials in ftp.config.json
* `npm start`
* Ignore unsafe connection in browser (live process.studio site is proxied at localhost)
* Changes are automatically uploaded to FTP on save


Just for reference, here's the original engines declaration in package.json:
```
"engines": {
  "node": "^4.2.1",
  "npm": "^3.3.8"
}
```
