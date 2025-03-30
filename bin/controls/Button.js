define('package/quiqqer/authapple/bin/controls/Button', [

    'qui/QUI',
    'qui/controls/Control',
    'css!package/quiqqer/authapple/bin/controls/Button.css'

], function (QUI, QUIControl) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/authapple/bin/controls/Button',

        Binds: [
            '$onImport'
        ],

        initialize: function (options) {
            this.parents(options);

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            console.log(this.getElm());
        }
    });
});
