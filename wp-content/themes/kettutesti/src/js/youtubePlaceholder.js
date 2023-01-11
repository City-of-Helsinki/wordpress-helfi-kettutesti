function initializeYoutubePlaceholder() {
    var placeholderTranslations = {
        fi: 'Tällä sivulla olevien tietojen näyttämiseen käytetään teknologiaa, joka asettaa analytiikkaevästeitä.' + '<br>' + 'Salli analytiikkaevästeet ' + "<a href='javascript:;' class='ch2-open-settings-btn' onClick='cookiehub.openSettings()'>asetuksissa</a>" + ' nähdäksesi tiedot.',
        en: 'The technology used for showing the information on this page uses analytical cookies.' + '<br>' + 'Allow analytical cookies in the ' + "<a href='javascript:;' class='ch2-open-settings-btn' onClick='cookiehub.openSettings()'>settings</a>" + ' to see the information.',
        sv: 'Uppgifterna på den här sidan visas med hjälp av teknik som utnyttjar analyskakor.' + '<br>' + 'Godkänn analyskakorna i ' + "<a href='javascript:;' class='ch2-open-settings-btn' onClick='cookiehub.openSettings()'>inställningarna</a>" + ' för att se uppgifterna.',
    }
    function setYoutubePlaceholders(element) {
        var lang = document.documentElement.lang
        var placeholderContainter = document.createElement("div");
        placeholderContainter.className = "ch2-settings-declaration-placeholder";
        placeholderContainter.setAttribute('data-consent', "analytics");
        placeholderContainter.setAttribute('data-inverse', '');
        placeholderContainter.setAttribute('data-display', '');
        if (lang) {
            placeholderTranslations[lang] !== undefined
                ? placeholderContainter.insertAdjacentHTML("beforeend", "<div><p>" + placeholderTranslations[lang] + "</div>")
                : placeholderContainter.insertAdjacentHTML("beforeend", "<div><p>" + placeholderTranslations[en] + "</div>")
        }
        var iframeContainter = document.createElement("div");
        iframeContainter.setAttribute('data-consent', "analytics");
        iframeContainter.setAttribute('data-display', '');
        element.parentNode.insertBefore(placeholderContainter, element);
        element.parentNode.insertBefore(iframeContainter, element);
        iframeContainter.append(element)
    }
    var iframe = document.getElementsByTagName("iframe");
    var youtubeContainer = document.getElementById('ytcontainer');
    for (var i = 0; i < iframe.length; i++) {
        var iframeSrc = iframe[i].dataset.src;
        if (iframeSrc !== undefined) {
            var youtube = iframeSrc.includes("youtube");
            if (youtube) {
                setYoutubePlaceholders(iframe[i]);
            }
        }
    }
    if (youtubeContainer) {
        setYoutubePlaceholders(youtubeContainer);
    }
}