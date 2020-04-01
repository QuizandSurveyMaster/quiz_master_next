/*! show-js-error | © 2020 Denis Seleznev | MIT License */
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    (global = global || self, global.showJSError = factory());
}(this, (function () { 'use strict';

    var showJSError = { // eslint-disable-line no-unused-vars
        /**
         * Initialize.
         *
         * @param {Object} [settings]
         * @param {String} [settings.title]
         * @param {String} [settings.userAgent]
         * @param {String} [settings.copyText]
         * @param {String} [settings.sendText]
         * @param {String} [settings.sendUrl]
         * @param {String} [settings.additionalText]
         * @param {Boolean} [settings.helpLinks]
         */
        init: function(settings) {
            if (this._inited) {
                return;
            }

            var that = this,
                isAndroidOrIOS = /(Android|iPhone|iPod|iPad)/i.test(navigator.userAgent);

            this.settings = settings || {};

            this._inited = true;
            this._isLast = true;
            this._i = 0;
            this._buffer = [];

            this._onerror = function(e) {
                if (isAndroidOrIOS && e && e.message === 'Script error.' && !e.lineno && !e.filename) {
                    return;
                }

                that._buffer.push(e);
                if (that._isLast) {
                    that._i = that._buffer.length - 1;
                }

                that._update();
            };

            if (window.addEventListener) {
                window.addEventListener('error', this._onerror, false);
            } else {
                this._oldOnError = window.onerror;

                window.onerror = function(message, filename, lineno, colno, error) {
                    that._onerror({
                        message: message,
                        filename: filename,
                        lineno: lineno,
                        colno: colno,
                        error: error
                    });

                    if (typeof that._oldOnError === 'function') {
                        that._oldOnError.apply(window, arguments);
                    }
                };
            }
        },
        /**
         * Destructor.
         */
        destruct: function() {
            if (!this._inited) { return; }

            if (window.addEventListener) {
                window.removeEventListener('error', this._onerror, false);
            } else {
                window.onerror = this._oldOnError || null;
                delete this._oldOnError;
            }

            if (document.body && this._container) {
                document.body.removeChild(this._container);
            }

            this._buffer = [];

            this._inited = false;
        },
        /**
         * Show error message.
         *
         * @param {String|Object|Error} err
         */
        show: function(err) {
            if (typeof err !== 'undefined') {
                this._buffer.push(typeof err === 'object' ? err : new Error(err));
            }

            this._update();
            this._show();
        },
        /**
         * Hide error message.
         */
        hide: function() {
            if (this._container) {
                this._container.className = this.elemClass('');
            }
        },
        /**
         * Copy error message to clipboard.
         */
        copyText: function() {
            var err = this._buffer[this._i],
                text = this._getDetailedMessage(err),
                body = document.body,
                textarea = this.elem({
                    name: 'textarea',
                    tag: 'textarea',
                    props: {
                        innerHTML: text
                    },
                    container: body
                });

            try {
                textarea.select();
                document.execCommand('copy');
            } catch (e) {
                alert('Copying text is not supported in this browser.');
            }

            body.removeChild(textarea);
        },
        /**
         * Create a elem.
         *
         * @param {Object} data
         * @param {String} data.name
         * @param {DOMElement} data.container
         * @param {String} [data.tag]
         * @param {Object} [data.props]
         * @returns {DOMElement}
         */
        elem: function(data) {
            var el = document.createElement(data.tag || 'div'),
                props = data.props;

            for (var i in props) {
                // eslint-disable-next-line no-prototype-builtins
                if (props.hasOwnProperty(i)) {
                    el[i] = props[i];
                }
            }

            el.className = this.elemClass(data.name);

            data.container.appendChild(el);

            return el;
        },
        /**
         * Build className for elem.
         *
         * @param {String} [name]
         * @param {String} [mod]
         * @returns {String}
         */
        elemClass: function(name, mod) {
            var cl = 'show-js-error';
            if (name) {
                cl += '__' + name;
            }

            if (mod) {
                cl += ' ' + cl + '_' + mod;
            }

            return cl;
        },
        /**
         * Escape HTML.
         *
         * @param {String} text
         * @returns {String}
         */
        escapeHTML: function(text) {
            return (text || '').replace(/[&<>"'/]/g, function(sym) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    '\'': '&#39;',
                    '/': '&#x2F;'
                }[sym];
            });
        },
        /**
         * Toggle view (shortly/detail).
         */
        toggleDetailed: function() {
            var body = this._body;
            if (body) {
                if (this._toggleDetailed) {
                    this._toggleDetailed = false;
                    body.className = this.elemClass('body');
                } else {
                    this._toggleDetailed = true;
                    body.className = this.elemClass('body', 'detailed');
                }
            }
        },
        _append: function() {
            var that = this;

            this._container = document.createElement('div');
            this._container.className = this.elemClass('');

            this._title = this.elem({
                name: 'title',
                props: {
                    innerHTML: this._getTitle()
                },
                container: this._container
            });

            this._body = this.elem({
                name: 'body',
                container: this._container
            });

            this._message = this.elem({
                name: 'message',
                props: {
                    onclick: function() {
                        that.toggleDetailed();
                    }
                },
                container: this._body
            });

            if (this.settings.helpLinks) {
                this._helpLinks = this.elem({
                    name: 'help',
                    container: this._body
                });

                this._mdn = this.elem({
                    tag: 'a',
                    name: 'mdn',
                    props: {
                        target: '_blank',
                        innerHTML: 'MDN'
                    },
                    container: this._helpLinks
                });

                this._stackoverflow = this.elem({
                    tag: 'a',
                    name: 'stackoverflow',
                    props: {
                        target: '_blank',
                        innerHTML: 'Stack Overflow'
                    },
                    container: this._helpLinks
                });
            }

            this._filename = this.elem({
                name: 'filename',
                container: this._body
            });

            if (this.settings.userAgent) {
                this._ua = this.elem({
                    name: 'ua',
                    container: this._body
                });
            }

            if (this.settings.additionalText) {
                this._additionalText = this.elem({
                    name: 'additional-text',
                    container: this._body
                });
            }

            this.elem({
                name: 'close',
                props: {
                    innerHTML: '×',
                    onclick: function() {
                        that.hide();
                    }
                },
                container: this._container
            });

            this._actions = this.elem({
                name: 'actions',
                container: this._container
            });

            this.elem({
                tag: 'input',
                name: 'copy',
                props: {
                    type: 'button',
                    value: this.settings.copyText || 'Copy',
                    onclick: function() {
                        that.copyText();
                    }
                },
                container: this._actions
            });

            if (this.settings.sendUrl) {
                this._sendLink = this.elem({
                    tag: 'a',
                    name: 'send-link',
                    props: {
                        href: '',
                        target: '_blank'
                    },
                    container: this._actions
                });

                this._send = this.elem({
                    tag: 'input',
                    name: 'send',
                    props: {
                        type: 'button',
                        value: this.settings.sendText || 'Send'
                    },
                    container: this._sendLink
                });
            }

            this._arrows = this.elem({
                tag: 'span',
                name: 'arrows',
                container: this._actions
            });

            this._prev = this.elem({
                tag: 'input',
                name: 'prev',
                props: {
                    type: 'button',
                    value: '←',
                    onclick: function() {
                        that._isLast = false;
                        if (that._i) {
                            that._i--;
                        }

                        that._update();
                    }
                },
                container: this._arrows
            });

            this._next = this.elem({
                tag: 'input',
                name: 'next',
                props: {
                    type: 'button',
                    value: '→',
                    onclick: function() {
                        that._isLast = false;
                        if (that._i < that._buffer.length - 1) {
                            that._i++;
                        }

                        that._update();
                    }
                },
                container: this._arrows
            });

            this._num = this.elem({
                tag: 'span',
                name: 'num',
                props: {
                    innerHTML: this._i + 1
                },
                container: this._arrows
            });

            var append = function() {
                document.body.appendChild(that._container);
            };

            if (document.body) {
                append();
            } else {
                if (document.addEventListener) {
                    document.addEventListener('DOMContentLoaded', append, false);
                } else if (document.attachEvent) {
                    document.attachEvent('onload', append);
                }
            }
        },
        _getDetailedMessage: function(err) {
            var settings = this.settings,
                screen = typeof window.screen === 'object' ? window.screen : {},
                orientation = screen.orientation || screen.mozOrientation || screen.msOrientation || '',
                props = [
                    ['Title', err.title || this._getTitle()],
                    ['Message', this._getMessage(err)],
                    ['Filename', this._getFilenameWithPosition(err)],
                    ['Stack', this._getStack(err)],
                    ['Page url', window.location.href],
                    ['Refferer', document.referrer],
                    ['User-agent', settings.userAgent || navigator.userAgent],
                    ['Screen size', [screen.width, screen.height, screen.colorDepth].join('×')],
                    ['Screen orientation', typeof orientation === 'string' ? orientation : orientation.type],
                    ['Cookie enabled', navigator.cookieEnabled]
                ];

            var text = '';
            for (var i = 0; i < props.length; i++) {
                var item = props[i];
                text += item[0] + ': ' + item[1] + '\n';
            }

            if (settings.templateDetailedMessage) {
                text = settings.templateDetailedMessage.replace(/\{message\}/, text);
            }

            return text;
        },
        _getExtFilename: function(e) {
            var filename = e.filename,
                html = this.escapeHTML(this._getFilenameWithPosition(e));

            if (filename && filename.search(/^(https?|file):/) > -1) {
                return '<a target="_blank" href="' +
                    this.escapeHTML(filename) + '">' + html + '</a>';
            } else {
                return html;
            }
        },
        _get: function(value, defaultValue) {
            return typeof value !== 'undefined' ? value : defaultValue;
        },
        _getFilenameWithPosition: function(e) {
            var text = e.filename || '';
            if (typeof e.lineno !== 'undefined') {
                text += ':' + this._get(e.lineno, '');
                if (typeof e.colno !== 'undefined') {
                    text += ':' + this._get(e.colno, '');
                }
            }

            return text;
        },
        _getMessage: function(e) {
            var msg = e.message;

            // IE
            if (e.error && e.error.name && 'number' in e.error) {
                msg = e.error.name + ': ' + msg;
            }

            return msg;
        },
        _getStack: function(err) {
            return (err.error && err.error.stack) || err.stack || '';
        },
        _getTitle: function() {
            return this.settings.title || 'JavaScript error';
        },
        _show: function() {
            this._container.className = this.elemClass('', 'visible');
        },
        _highlightLinks: function(text) {
            return text.replace(/(at | \(|@)(https?|file)(:.*?)(?=:\d+:\d+\)?$)/gm, function($0, $1, $2, $3) {
                var url = $2 + $3;

                return $1 + '<a target="_blank" href="' + url + '">' + url + '</a>';
            });
        },
        _update: function() {
            if (!this._appended) {
                this._append();
                this._appended = true;
            }

            var e = this._buffer[this._i],
                stack = this._getStack(e),
                filename;

            if (stack) {
                filename = this._highlightLinks(this.escapeHTML(stack));
            } else {
                filename = this._getExtFilename(e);
            }

            this._title.innerHTML = this.escapeHTML(e.title || this._getTitle());

            this._message.innerHTML = this.escapeHTML(this._getMessage(e));

            this._filename.innerHTML = filename;

            if (this._ua) {
                this._ua.innerHTML = this.escapeHTML(this.settings.userAgent);
            }

            if (this._additionalText) {
                this._additionalText.innerHTML = this.escapeHTML(this.settings.additionalText);
            }

            if (this._sendLink) {
                this._sendLink.href = this.settings.sendUrl
                    .replace(/\{title\}/, encodeURIComponent(this._getMessage(e)))
                    .replace(/\{body\}/, encodeURIComponent(this._getDetailedMessage(e)));
            }

            if (this._buffer.length > 1) {
                this._arrows.className = this.elemClass('arrows', 'visible');
            }

            if (this._helpLinks) {
                this._mdn.href = 'https://developer.mozilla.org/en-US/search?q=' + encodeURIComponent(e.message || e.stack || '');
                this._stackoverflow.href = 'https://stackoverflow.com/search?q=' + encodeURIComponent('[js] ' + (e.message || e.stack || ''));
            }

            this._prev.disabled = !this._i;
            this._num.innerHTML = (this._i + 1) + '&thinsp;/&thinsp;' + this._buffer.length;
            this._next.disabled = this._i === this._buffer.length - 1;

            this._show();
        }
    };

    return showJSError;

})));
