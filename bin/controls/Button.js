define('package/quiqqer/authapple/bin/controls/Button', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/authapple/bin/Apple',
    'package/quiqqer/frontend-users/bin/Registration',

    'css!package/quiqqer/authapple/bin/controls/Button.css'

], function (QUI, QUIControl, Apple, registration) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/authapple/bin/controls/Button',

        Binds: [
            '$click',
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

            this.getElm().addEventListener('click', this.$click);
            this.getElm().disabled = false;
        },

        $click: function (event) {
            const target = event.target;
            let button = target;
            let token = null;

            if (button.nodeName !== 'BUTTON') {
                button = target.closest('button');
            }

            let icon = button.querySelector('.fa');

            button.disabled = true;
            icon.classList.remove('fa-brands', 'fa-apple');
            icon.classList.add('fa-spinner', 'fa-spin');

            // token form
            const form = button.closest('form');

            // nodes
            let Registration = null;
            let Login = null;

            const registrationNode = button.closest(
                '[data-qui="package/quiqqer/frontend-users/bin/frontend/controls/Registration"]'
            );

            if (registrationNode) {
                Registration = QUI.Controls.getById(registrationNode.get('data-quiid'));
            }

            const loginNode = button.closest('[data-qui="controls/users/Login"]');

            if (loginNode) {
                Login = QUI.Controls.getById(loginNode.get('data-quiid'));
            }


            Apple.authenticate().then(() => {
                return Apple.getToken();
            }).then((tokenResult) => {
                token = tokenResult;

                if (form) {
                    let tokenNode = form.querySelector('input[name="token"]');

                    if (tokenNode) {
                        tokenNode.parentNode.removeChild(tokenNode);
                    }

                    tokenNode = document.createElement('input');
                    tokenNode.type = 'hidden';
                    tokenNode.name = 'token';
                    tokenNode.value = token;
                    form.appendChild(tokenNode);
                }

                return Apple.isAccountConnectedToQuiqqer(token);
            }).then((isConnected) => {
                // if yes: login
                if (!isConnected) {
                    // if not: registration
                    if (Registration) {
                        return Registration.$sendForm(form);
                    }

                    return registration.register(
                        'QUI\\Apple\\Registrar',
                        {token: token}
                    );
                }
            }).then(() => {
                form.setAttribute('data-authenticator', 'QUI\\Apple\\Auth');

                if (Login) {
                    return Login.auth(form);
                }
            }).catch(() => {
                icon.classList.add('fa-brands', 'fa-apple');
                icon.classList.remove('fa-spinner', 'fa-spin');
                button.disabled = false;
            });
        }
    });
});

