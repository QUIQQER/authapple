define('package/quiqqer/authapple/bin/controls/Button', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/authapple/bin/Apple',

    'css!package/quiqqer/authapple/bin/controls/Button.css'

], function (QUI, QUIControl, Apple) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/authapple/bin/controls/Button',

        Binds: [
            '$onImport',
            'authenticate'
        ],

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            const form = this.getElm().getParent('form');

            form.addEventListener('submit', function (event) {
                event.stopPropagation();
                event.preventDefault();
            });

            this.getElm().addEventListener('click', this.authenticate);
        },

        authenticate: function () {

            Apple.authenticate().then(() => {
                console.log('booom');
            });

        }
    });
});
