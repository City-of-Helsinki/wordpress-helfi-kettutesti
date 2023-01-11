$(document).ready(function () {
    var cpm = {
      onInitialise: function (status) { //Event which starts when cookiehub script is loaded
        initializeYoutubePlaceholder(); //Optional function for hel.fi to add youtube placeholder tags
        setTimeout(() => { //Function to improve cookiehub banner accessibility
          document
            .getElementById('ch2-dialog-title')
            .setAttribute('tabindex', 0);
          document
            .getElementById('ch2-dialog-description')
            .setAttribute('tabindex', 0);
          document
            .getElementsByClassName('ch2-dialog')[0]
            .setAttribute('tabindex', 0);
        }, 0);
      },
      language: document.documentElement.lang, //For changing the cookiehub interface language depending on the page language
      enabled: location.href.indexOf('/embed/') > -1 ? false : true, //hiding cookiehub interface on iframes that have hel.fi cookiehub
      elements: { //Everything below enhances youtube accessibility (this code was recently added)
        dialog: {
          line1: '<h2 id="ch2-dialog-title">{{title}}</h2>',
        },
        settings: {
          header:
            '<div class="ch2-settings-header"><a href="#" class="ch2-close-settings-btn" aria-label="{{close}}"></a><h2 id="ch2-settings-title" tabindex="0">{{title}}</h2></div>',
          options: {
            details:
              '<div class="ch2-settings-option-details"><h3 id="ch2-{{id}}-title">{{title}}</h3><p>{{text}}</p></div>',
          },
        },
        declaration: {
          categories: {
            details: '<h3 class="ch2-header">{{title}}</h3><p>{{text}}</p>',
          },
        },
      },
    };
    //Main launch script
    (function (h, u, b) {
      var d = h.getElementsByTagName('script')[0],
        e = h.createElement('script');
      e.async = true;
      e.src = 'https://cookiehub.net/c2/c7e96adf.js';
      e.onload = function () {
        u.cookiehub.load(b);
      };
      d.parentNode.insertBefore(e, d);
    })(document, window, cpm);
  });