define('package/quiqqer/authapple/bin/classes/Apple', [

    'qui/QUI',
    'qui/classes/DOM',
    'package/quiqqer/authapple/bin/controls/Button',
    'Ajax',

    'css!package/quiqqer/authapple/bin/classes/Apple.css'

], function (QUI, QDOM, AppleButton, QUIAjax) {
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

        getButton: function () {
            return new AppleButton();
        },

        authenticate: function () {
            console.log('authenticate');


            return this.loadAppleScript().then(() => {
                console.log('get client');
                return this.getClientId();
            }).then(() => {
                if (typeof window.AppleID === 'undefined') {
                    return Promise.reject('AppleID is not defined');
                }

                const redirectURI = window.location.origin + URL_OPT_DIR + 'quiqqer/authapple/bin/oauth_callback.php';

                AppleID.auth.init({
                    clientId: this.$clientId,
                    scope: 'name email',
                    redirectURI: redirectURI,
                    usePopup: true
                });

                return AppleID.auth.signIn().then((response) => {
                    console.log('Apple Response:', response);
                    // response.authorization.code (für Backend)
                    // response.authorization.id_token (optional, für JWT-Daten)

                    this.$token = response.authorization.id_token;
                    this.$code = response.authorization.code;
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


                // Workaround für AMD/RequireJS-Konflikt
                let oldDefine = window.define;
                window.define = undefined;

                const script = document.createElement('script');
                script.src = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';
                script.async = true;
                script.defer = true;
                script.onload = function () {
                    window.define = oldDefine; // restore define
                    resolve();
                };
                script.onerror = function () {
                    window.define = oldDefine;
                    reject();
                };
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
        },

        /**
         * Get Apple id_token for currently connected Apple account
         *
         * @return {Promise}
         */
        getToken: function () {
            if (this.$token) {
                return Promise.resolve(this.$token);
            }

            return this.authenticate();
        },

        /**
         * Get info of Apple profile
         *
         * @return {Promise}
         */
        getProfileInfo: function (token) {
            return new Promise((resolve, reject) => {
                QUIAjax.post('package_quiqqer_authapple_ajax_getDataByToken', resolve, {
                    'package': 'quiqqer/authapple',
                    idToken: token,
                    onError: reject
                });
            });
        },

        /**
         * Connect a Apple account with a quiqqer account
         *
         * @param {number} userId - QUIQQER User ID
         * @param {string} idToken - Apple id_token
         * @return {Promise}
         */
        connectQuiqqerAccount: function (userId, idToken) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_authapple_ajax_connectAccount', resolve, {
                    'package': 'quiqqer/authapple',
                    userId: userId,
                    idToken: idToken,
                    onError: reject
                });
            });
        },

        /**
         * Connect a Apple account with a quiqqer account
         *
         * @param {number} userId - QUIQQER User ID
         * @return {Promise}
         */
        disconnectQuiqqerAccount: function (userId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_authapple_ajax_disconnectAccount', resolve, {
                    'package': 'quiqqer/authapple',
                    userId: userId,
                    onError: reject
                });
            });
        },

        /**
         * Get details of connected Apple account based on QUIQQER User ID
         *
         * @param {number} userId - QUIQQER User ID
         * @return {Promise}
         */
        getAccountByQuiqqerUserId: function (userId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_authapple_ajax_getAccountByQuiqqerUserId', resolve, {
                    'package': 'quiqqer/authapple',
                    userId: userId,
                    onError: reject
                });
            });
        },

        /**
         * Check if Apple account is connected to a QUIQQER account
         *
         * @param {string} idToken - Apple API id_token
         * @return {Promise}
         */
        isAccountConnectedToQuiqqer: function (idToken) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_authapple_ajax_isAppleAccountConnected', resolve, {
                    'package': 'quiqqer/authapple',
                    idToken: idToken,
                    onError: reject
                });
            });
        }
    });
});