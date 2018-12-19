(function() {
  'use strict';
  const selector = '#emoji-map';
  const sheetURL = '/wp-content/uploads/2018/12/sheet_training_emoji1_100_noborder_q40.jpg';
  const sheetCols = 100;
  const sheetRows = 100;
  const sheetElementSize = 100; // needed for saving
  
  const mapSize = 1000;
  const loupeSize = 100;
  const loupeOffset = {x:10, y:0};
  const zoomFactor = 3;
  // const loupeZoomFactor = 1.5;
  const saveSize = 300;
  
  document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(selector);
    if (container) {
      console.log('emoji-map: init on ' + selector);
    } else {
      console.warn('emoji-map: ' + selector + ' not found. quitting');
      return;
    }
    
    const style = document.createElement('style');
    style.innerHTML = selector + ":fullscreen {background-color:white; overflow:scroll; padding:150px 0;}"
    document.querySelector('body').appendChild(style);
    
    let map = document.createElement('div');
    map.style.width = mapSize + 'px';
    map.style.height = mapSize + 'px';
    map.style.backgroundSize = map.style.width + " " + map.style.height;
    map.style.backgroundImage = 'url(' + sheetURL + ')';
    map.style.backgroundRepeat = 'no-repeat';
    map.style.margin = "0 auto";
    container.appendChild(map);
    
    const follower = document.createElement('div');
    const lastIdx = {x:0, y:0};
    follower.style.position = 'fixed';
    follower.style.display = 'none';
    follower.style.width = loupeSize + 'px';
    follower.style.height = loupeSize + 'px';
    follower.style.borderRadius = loupeSize/2 + 'px';
    follower.style.backgroundSize = loupeSize*sheetCols + 'px ' + loupeSize*sheetRows + 'px';
    follower.style.backgroundImage = 'url(' + sheetURL + ')';
    follower.style.backgroundRepeat = 'no-repeat';
    container.appendChild(follower);
    
    const canvas = document.createElement('canvas');
    canvas.width = 300;
    canvas.height = 300;
    const ctx = canvas.getContext('2d');
    const img = document.createElement('img');
    img.src = sheetURL;
    
    function updateFollower(e) {
      // console.log(e.offsetX, e.offsetY);
      follower.style.left = e.clientX + loupeOffset.x + 'px';
      // if (e.offsetX > mapSize-(loupeSize+loupeOffset.x)) {
      //   follower.style.left = e.clientX - (loupeSize + loupeOffset.x) + 'px';
      // }
      follower.style.top  = e.clientY + loupeOffset.y + 'px';
      
      lastIdx.x = Math.floor( e.offsetX / (mapSize/sheetCols) );
      lastIdx.y = Math.floor( e.offsetY / (mapSize/sheetRows) );
      // console.log(idx_x, idx_y);
      let sheet_x = -lastIdx.x * loupeSize;
      let sheet_y = -lastIdx.y * loupeSize;
      follower.style.backgroundPosition = sheet_x + 'px ' + sheet_y + 'px';
    }
    
    function adjustPosition(e, forceReset=0) {
      // console.log(e.buttons);
      if (e.buttons == 1 && !forceReset) { // LMB is pressed
        map.style.backgroundSize = mapSize*zoomFactor + "px " + mapSize*zoomFactor + "px";
        let zx = e.offsetX * (zoomFactor-1);
        let zy = e.offsetY * (zoomFactor-1);
        map.style.backgroundPosition = -zx + "px " + -zy + "px";
      } else {
        map.style.backgroundSize = mapSize + "px " + mapSize + "px";
        map.style.backgroundPosition = "0 0";
      }
    }
    
    map.addEventListener("mousemove", (e) => {
      updateFollower(e);
      adjustPosition(e);
    });
    map.addEventListener("mouseenter", (e) => {
      updateFollower(e);
      follower.style.display = 'block';
      adjustPosition(e);
    });
    map.addEventListener("mouseleave", (e) => {
      follower.style.display = 'none';
      adjustPosition(e, 1);
    });
    map.addEventListener("mousedown", (e) => {
      adjustPosition(e);
      e.preventDefault();
      if (e.button == 2) save();
    });
    map.addEventListener("mouseup", (e) => {
      adjustPosition(e);
      e.preventDefault();
    });
    map.addEventListener("contextmenu", (e) => {
      e.preventDefault();
    });
    
    map.addEventListener("dblclick", fullscreen);
    
    function fullscreen() {
      container.requestFullscreen();
    }
    
    function save() {
      ctx.drawImage(img,
        lastIdx.x * sheetElementSize, lastIdx.y * sheetElementSize, sheetElementSize, sheetElementSize, 
        0, 0, saveSize, saveSize);
      const url = canvas.toDataURL();
      const a = document.createElement('a');
      a.href = url;
      a.download = 'aimoji_' + lastIdx.x + '_' + lastIdx.y + '.png';
      a.click();
    }
    
    document.querySelector('body').addEventListener('keydown', (e) => {
      if (e.key == 'f') fullscreen();
      else if (e.key == 's') save(e);
    });
  });
})();
