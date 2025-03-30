define('package/quiqqer/authapple/bin/classes/Apple', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax',

    'css!package/quiqqer/authapple/bin/classes/Apple.css'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({

        Extends: QDOM,
        Type: 'package/quiqqer/authapple/bin/classes/Apple',

        Binds: [],
        options: {},

        initialize: function (options) {
            this.parent(options);

            this.$token = null;
            this.$code = null;
            this.$clientId = null;
        },

        authenticate: function () {
            return this.loadAppleScript().then(() => {
                return this.getClientId();
            }).then(() => {
                if (typeof window.AppleID === 'undefined') {
                    return;
                }

                const redirectURI = window.location.origin + URL_OPT_DIR + 'quiqqer/authapple/oauth/bin/callback.php';

                AppleID.auth.init({
                    clientId: this.$clientId,
                    scope: 'name email',
                    redirectURI: redirectURI,
                    usePopup: true
                });

                AppleID.auth.signIn().then((response) => {
                    console.log('Apple Response:', response);
                    // response.authorization.code (für Backend)
                    // response.authorization.id_token (optional, für JWT-Daten)

                    this.$token = response.authorization.id_token;
                    this.$code = response.authorization.code;

                }).catch((error) => {
                    console.error('Apple Login Fehler:', error);
                });
            });
        },

        loadAppleScript: function () {
            return new Promise((resolve, reject) => {
                const existing = document.querySelector(
                    'script[src*="appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"]'
                );

                if (existing) {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';
                script.async = true;
                script.defer = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            }).then(() => {
                this.$loaded = true;
            });
        },

        /**
         * Get Client-ID for Apple API requests
         *
         * @return {Promise}
         */
        getClientId: function () {
            if (this.$clientId) {
                return Promise.resolve(this.$clientId);
            }

            return new Promise((resolve, reject) => {
                QUIAjax.get('package_quiqqer_authapple_ajax_getClientId', (clientId) => {
                    this.$clientId = clientId;
                    resolve(clientId);
                }, {
                    'package': 'quiqqer/authapple',
                    onError: reject
                });
            });
        }

    });
});