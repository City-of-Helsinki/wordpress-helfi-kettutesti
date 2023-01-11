export class SkipToContent {

    private $skipToContentLink: JQuery;
    private $content: JQuery;
    private headerSelector: string;

    constructor(contentID: string, linkText: string, headerSelector: string) {

        this.$skipToContentLink = $('<a tabindex="0" class="skip-to-content" href="' + contentID + '">' + linkText + '</a>');
        $('body').prepend(this.$skipToContentLink);
    }
}