// ==UserScript==
// @name         Recaptcha Solver
// @namespace    GoogleRecaptcha
// @version      0.1
// @description  try to take over the world!
// @author       Tackyou
// @match        https://www.google.com/recaptcha/api2/*
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
    
    var dlLink = null;

    if(location.href.indexOf('anchor')>-1){
        initCaptcha();
    }
    
    function initCaptcha(){
        var captcha = setInterval(function(){
            if(document.querySelectorAll('.recaptcha-checkbox-checkmark').length>0){
                clearInterval(captcha);
                document.querySelector('.recaptcha-checkbox-checkmark').click();
                console.log('[RC] anchor');
            }
        }, 100);
    }
    
    if(location.href.indexOf('frame')>-1){
        console.log('[RC] frame');
        var audio = setInterval(function(){
            if(document.querySelector('.rc-audiochallenge-download-link') !== null){
                console.log('[RC] audio found');
                clearInterval(audio);
                setInterval(function(){
                    var newLink = document.querySelector('.rc-audiochallenge-download-link').href;
                    if(newLink.length>0 && newLink != dlLink){
                        dlLink = newLink;
                        console.log('[RC] sending audio');
                        var request = new XMLHttpRequest();
                        request.open('POST', 'https://127.0.0.1/recaptcha/index.php', true);
                        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                        request.onreadystatechange = function() {
                            if(request.readyState == 4 && request.status == 200) {
                                console.log('[RC] audio result received');
                                var result = request.responseText;
                                if(result.length>0){
                                    var json = null;
                                    try {
                                        json = JSON.parse(result);
                                    } catch (e) {
                                    }
                                    if(json !== null && json.confidence > 0.7){
                                        document.getElementById('audio-response').value = json.result;
                                        //document.getElementById('recaptcha-verify-button').click();
                                    }else{
                                        document.getElementById('audio-response').value = '???';
                                    }
                                }
                            }
                        };
                        request.send('audiocaptcha='+encodeURIComponent(dlLink).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+'));
                    }
                }, 100);
            }
        }, 100);
    }
    /*
    function click(x, y)
    {
        var myLayer = document.createElement('div');
        myLayer.style.position = 'absolute';
        myLayer.style.left = x+'px';
        myLayer.style.top = y+'px';
        myLayer.style.width = '2px';
        myLayer.style.height = '2px';
        myLayer.style.background = 'red';
        myLayer.style.zIndex = '9999999';
        myLayer.style.border = '3px double #FFF';
        myLayer.style.borderRadius = '10px';
        myLayer.style.pointerEvents = 'none';
        document.body.appendChild(myLayer);
     
        var ev = new MouseEvent('click', {
            'view': window,
            'bubbles': true,
            'cancelable': true,
            'screenX': x,
            'screenY': y
        });

        var el = document.elementFromPoint(x, y);

        el.dispatchEvent(ev);
        
        myLayer.click();
        
        myLayer.dispatchEvent(ev);
        
        console.log('click x:'+x+' y:'+y);
    }*/

})();