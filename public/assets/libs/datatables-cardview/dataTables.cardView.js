/**
 * DataTables CardView Plugin v1.0.0
 * 
 * Inspired by official DataTables CardView extension.
 * Provides responsive card layouts using customizable HTML templates (<template id="...">)
 * or automatic card generation for DataTables with full pagination, search, and server-side support.
 * 
 * Compatible with DataTables 1.10+, 2.x and Bootstrap 4 / AdminLTE 3.
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }
            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net')(root, $).$;
            }
            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document, undefined) {
    'use strict';

    var DataTable = $.fn.dataTable;

    /**
     * CardView constructor
     * @param {DataTable.Api|object} dt DataTable API instance or settings object
     * @param {object} [options] Configuration options
     */
    var CardView = function (dt, options) {
        var dtApi = new DataTable.Api(dt);
        var settings = dtApi.settings()[0];

        // Ensure single instance per table
        if (settings._cardView) {
            return settings._cardView;
        }

        this.dt = dtApi;
        this.settings = settings;
        this.table = $(dtApi.table().node());
        this.wrapper = $(dtApi.table().container());

        // Merge options with defaults
        this.options = $.extend(true, {}, CardView.defaults, options);

        this._active = false;
        this._manualMode = null; // true, false, or null (auto by breakpoint)
        this._resizeTimeout = null;

        settings._cardView = this;

        this._init();
    };

    CardView.defaults = {
        enable: true,                    // Enable CardView extension
        breakpoint: 768,                 // Auto switch to cards if window width <= breakpoint (px). Set null/false for manual only.
        template: null,                  // Template selector (e.g. '#card-template') or function(data, rowIdx, rowNode, settings)
        gridClass: 'col-12 mb-2',        // Bootstrap grid column class for each card
        containerClass: 'row dt-cardview-container', // Wrapper class for cards
        emptyMessage: null,              // Custom empty message (defaults to DataTable emptyTable string)
        active: null,                    // Force initial active state (true/false/null)
        onCardRender: null               // Callback function(cardElement, rowData, rowIdx, rowNode)
    };

    $.extend(CardView.prototype, {
        /**
         * Initialize plugin DOM and events
         * @private
         */
        _init: function () {
            var self = this;

            // Create container for cards
            this._container = $('<div>')
                .addClass(this.options.containerClass)
                .addClass('dt-cardview-container')
                .hide();

            // Insert container directly after table node
            this.table.after(this._container);

            // Bind DataTables draw event
            this.dt.on('draw.dt.dtcv', function () {
                if (self._active) {
                    self._renderCards();
                }
            });

            // Bind DataTables destroy event
            this.dt.on('destroy.dt.dtcv', function () {
                self.destroy();
            });

            // Handle window resize for breakpoint responsiveness
            if (this.options.breakpoint) {
                $(window).on('resize.dtcv_' + this.settings.sTableId, function () {
                    if (self._resizeTimeout) {
                        clearTimeout(self._resizeTimeout);
                    }
                    self._resizeTimeout = setTimeout(function () {
                        self._checkBreakpoint();
                    }, 120);
                });
            }

            // Initial state evaluation
            if (this.options.active !== null) {
                this._manualMode = !!this.options.active;
                this._setActive(this._manualMode);
            } else {
                this._checkBreakpoint();
            }
        },

        /**
         * Evaluate screen width against breakpoint
         * @private
         */
        _checkBreakpoint: function () {
            if (this._manualMode !== null) {
                // Manual mode overrides automatic breakpoint
                return;
            }

            if (!this.options.breakpoint) {
                return;
            }

            var winWidth = $(window).width();
            var shouldBeActive = winWidth <= this.options.breakpoint;

            if (shouldBeActive !== this._active) {
                this._setActive(shouldBeActive);
            }
        },

        /**
         * Set CardView active status
         * @param {boolean} active
         * @private
         */
        _setActive: function (active) {
            this._active = !!active;

            if (this._active) {
                this.table.hide();
                this._container.show();
                this._renderCards();
                this.table.trigger('cardView.active', [this]);
            } else {
                this._container.empty().hide();
                this.table.show();
                this.table.trigger('cardView.inactive', [this]);
            }
        },

        /**
         * Render card elements from current page data
         * @private
         */
        _renderCards: function () {
            var self = this;
            var dt = this.dt;
            var settings = this.settings;
            var rows = dt.rows({ page: 'current' });
            var dataCount = rows.count();

            this._container.empty();

            if (dataCount === 0) {
                var emptyMsg = this.options.emptyMessage || 
                    (settings.oLanguage && settings.oLanguage.sEmptyTable) || 
                    'Data tidak ditemukan';

                var $empty = $('<div class="col-12"></div>')
                    .append($('<div class="dt-cardview-empty"></div>').html(emptyMsg));
                this._container.append($empty);
                return;
            }

            // Process each row
            rows.every(function (rowIdx, tableLoop, rowLoop) {
                var rowData = this.data();
                var rowNode = this.node();

                var $card = self._buildCard(rowData, rowIdx, rowNode);
                if ($card) {
                    var $col = $('<div>').addClass(self.options.gridClass);
                    $col.append($card);
                    self._container.append($col);
                }
            });
        },

        /**
         * Build single card element using template or fallback
         * @param {object} rowData
         * @param {number} rowIdx
         * @param {HTMLElement} rowNode
         * @returns {jQuery}
         * @private
         */
        _buildCard: function (rowData, rowIdx, rowNode) {
            var cardEl = null;

            if (typeof this.options.template === 'function') {
                var res = this.options.template(rowData, rowIdx, rowNode, this.settings);
                cardEl = $(res);
            } else if (typeof this.options.template === 'string' && $(this.options.template).length) {
                cardEl = this._renderFromTemplate(this.options.template, rowData, rowIdx, rowNode);
            } else {
                cardEl = this._renderDefaultCard(rowData, rowIdx, rowNode);
            }

            if (cardEl && cardEl.length) {
                cardEl.addClass('dt-cardview-card');
                cardEl.attr('data-dt-row', rowIdx);

                if (typeof this.options.onCardRender === 'function') {
                    this.options.onCardRender(cardEl, rowData, rowIdx, rowNode);
                }
            }

            return cardEl;
        },

        /**
         * Render card from HTML <template> or DOM selector
         * @param {string} selector
         * @param {object} rowData
         * @param {number} rowIdx
         * @param {HTMLElement} rowNode
         * @returns {jQuery}
         * @private
         */
        _renderFromTemplate: function (selector, rowData, rowIdx, rowNode) {
            var self = this;
            var templateNode = $(selector)[0];
            if (!templateNode) {
                return null;
            }

            var htmlContent = templateNode.tagName.toLowerCase() === 'template' 
                ? (templateNode.innerHTML || $(templateNode).html()) 
                : $(templateNode).html();

            // 1. Process string placeholders {field} or {{field}} in raw template
            htmlContent = htmlContent.replace(/\{\{?([a-zA-Z0-9_\-]+)\}?\}/g, function (match, key) {
                var v = self._getColumnValue(key, rowData, null);
                return (v !== undefined && v !== null && v !== '') ? v : match;
            });

            var $card = $('<div>').html(htmlContent);

            // 2. Process dataSrc/field elements: [data-dtcv-dataSrc], [data-dtcv-field]
            $card.find('[data-dtcv-dataSrc], [data-dtcv-field]').each(function () {
                var $el = $(this);
                var fieldKey = $el.attr('data-dtcv-dataSrc') || $el.attr('data-dtcv-field');
                var val = self._getColumnValue(fieldKey, rowData, rowNode);

                var tag = $el.prop('tagName').toLowerCase();
                if (tag === 'input' || tag === 'select' || tag === 'textarea') {
                    $el.val(val);
                } else if (tag === 'img') {
                    $el.attr('src', val);
                } else {
                    $el.html(val !== undefined && val !== null ? val : '');
                }
            });

            // 3. Process column title elements: [data-dtcv-title]
            $card.find('[data-dtcv-title]').each(function () {
                var $el = $(this);
                var fieldKey = $el.attr('data-dtcv-title');
                var title = self._getColumnTitle(fieldKey);
                $el.text(title);
            });

            return $card.children().first();
        },

        /**
         * Render default card if no template is specified
         * @param {object} rowData
         * @param {number} rowIdx
         * @param {HTMLElement} rowNode
         * @returns {jQuery}
         * @private
         */
        _renderDefaultCard: function (rowData, rowIdx, rowNode) {
            var self = this;
            var columns = this.settings.aoColumns;
            var $card = $('<div class="dt-cardview-default-card"></div>');
            var $body = $('<div></div>');
            var actionHtml = '';

            for (var i = 0; i < columns.length; i++) {
                var col = columns[i];
                if (!col.bVisible) continue;

                var title = self._getColumnTitle(i);
                var val = self._getColumnValue(i, rowData, rowNode);

                // If column is action/options, save for bottom
                var isActionCol = (title && /action|opsi|aksi/i.test(title)) || (col.mData === null && i === columns.length - 1);
                if (isActionCol) {
                    actionHtml = val;
                    continue;
                }

                var $row = $('<div class="dt-cardview-row"></div>');
                $row.append($('<div class="dt-cardview-label"></div>').text(title));
                $row.append($('<div class="dt-cardview-value"></div>').html(val));
                $body.append($row);
            }

            $card.append($body);

            if (actionHtml) {
                var $footer = $('<div class="mt-2 pt-2 border-top text-right"></div>').html(actionHtml);
                $card.append($footer);
            }

            return $card;
        },

        /**
         * Resolve column value, preferring rendered HTML from rowNode <td> if available
         * @param {string|number} key Column name or index
         * @param {object} rowData
         * @param {HTMLElement} rowNode
         * @returns {*}
         * @private
         */
        _getColumnValue: function (key, rowData, rowNode) {
            var colIdx = this._findColumnIndex(key);

            // Prefer rendered HTML from rowNode table cell
            if (colIdx !== -1 && rowNode) {
                var $td = $(rowNode).children('td').eq(colIdx);
                if ($td.length) {
                    return $td.html();
                }
            }

            // Fallback to rowData property
            if (rowData && typeof rowData === 'object') {
                if (key in rowData) {
                    return rowData[key];
                }
                if (colIdx !== -1) {
                    var col = this.settings.aoColumns[colIdx];
                    if (col && typeof col.mData === 'string' && col.mData in rowData) {
                        return rowData[col.mData];
                    }
                }
            }

            return '';
        },

        /**
         * Get column title from <th> or aoColumns
         * @param {string|number} key
         * @returns {string}
         * @private
         */
        _getColumnTitle: function (key) {
            var colIdx = this._findColumnIndex(key);
            if (colIdx === -1) return key;

            var col = this.settings.aoColumns[colIdx];
            if (col && col.sTitle) {
                return $('<div>').html(col.sTitle).text().trim();
            }

            if (this.settings.aoHeader && this.settings.aoHeader[0] && this.settings.aoHeader[0][colIdx]) {
                return $(this.settings.aoHeader[0][colIdx].cell).text().trim();
            }

            return 'Kolom ' + (colIdx + 1);
        },

        /**
         * Find column index by name, dataSrc, or integer
         * @param {string|number} key
         * @returns {number}
         * @private
         */
        _findColumnIndex: function (key) {
            if (typeof key === 'number') {
                return key >= 0 && key < this.settings.aoColumns.length ? key : -1;
            }

            if (!isNaN(key) && String(parseInt(key, 10)) === String(key)) {
                var idx = parseInt(key, 10);
                return idx >= 0 && idx < this.settings.aoColumns.length ? idx : -1;
            }

            var columns = this.settings.aoColumns;
            for (var i = 0; i < columns.length; i++) {
                if (columns[i].mData === key || columns[i].sName === key) {
                    return i;
                }
            }

            return -1;
        },

        /**
         * Public method: Enable card view
         */
        enable: function () {
            this._manualMode = true;
            this._setActive(true);
            return this;
        },

        /**
         * Public method: Disable card view (return to table)
         */
        disable: function () {
            this._manualMode = false;
            this._setActive(false);
            return this;
        },

        /**
         * Public method: Toggle between card view and table view
         */
        toggle: function () {
            if (this._active) {
                this.disable();
            } else {
                this.enable();
            }
            return this;
        },

        /**
         * Public method: Check if card view is currently active
         * @returns {boolean}
         */
        active: function () {
            return this._active;
        },

        /**
         * Public method: Redraw card view
         */
        redraw: function () {
            if (this._active) {
                this._renderCards();
            }
            return this;
        },

        /**
         * Destroy plugin instance and cleanup DOM/listeners
         */
        destroy: function () {
            $(window).off('resize.dtcv_' + this.settings.sTableId);
            this.dt.off('.dtcv');
            if (this._container) {
                this._container.remove();
            }
            this.table.show();
            delete this.settings._cardView;
        }
    });

    // Expose CardView constructor
    DataTable.CardView = CardView;
    $.fn.DataTable.CardView = CardView;

    // Register DataTables API methods
    DataTable.Api.register('cardView()', function () {
        var ctx = this.context[0];
        return ctx ? ctx._cardView : null;
    });

    DataTable.Api.register('cardView.enable()', function () {
        return this.iterator('table', function (settings) {
            if (settings._cardView) {
                settings._cardView.enable();
            }
        });
    });

    DataTable.Api.register('cardView.disable()', function () {
        return this.iterator('table', function (settings) {
            if (settings._cardView) {
                settings._cardView.disable();
            }
        });
    });

    DataTable.Api.register('cardView.toggle()', function () {
        return this.iterator('table', function (settings) {
            if (settings._cardView) {
                settings._cardView.toggle();
            }
        });
    });

    DataTable.Api.register('cardView.active()', function () {
        var ctx = this.context[0];
        return ctx && ctx._cardView ? ctx._cardView.active() : false;
    });

    DataTable.Api.register('cardView.redraw()', function () {
        return this.iterator('table', function (settings) {
            if (settings._cardView) {
                settings._cardView.redraw();
            }
        });
    });

    // Register Buttons if Buttons extension exists
    if (DataTable.ext && DataTable.ext.buttons) {
        DataTable.ext.buttons.cardView = {
            text: '<i class="fas fa-th-large"></i> Kartu',
            className: 'btn-dt-cardview',
            action: function (e, dt, node, config) {
                dt.cardView.enable();
                $(node).addClass('btn-dt-cardview-active').siblings().removeClass('btn-dt-cardview-active');
            }
        };

        DataTable.ext.buttons.tableView = {
            text: '<i class="fas fa-table"></i> Tabel',
            className: 'btn-dt-tableview',
            action: function (e, dt, node, config) {
                dt.cardView.disable();
                $(node).addClass('btn-dt-cardview-active').siblings().removeClass('btn-dt-cardview-active');
            }
        };

        DataTable.ext.buttons.cardViewToggle = {
            text: '<i class="fas fa-th-large"></i> Tampilan',
            className: 'btn-dt-cardview-toggle',
            action: function (e, dt, node, config) {
                dt.cardView.toggle();
                var active = dt.cardView.active();
                if (active) {
                    $(node).addClass('btn-dt-cardview-active');
                } else {
                    $(node).removeClass('btn-dt-cardview-active');
                }
            }
        };
    }

    // Auto-initialize via DataTables initialization options: cardView: true or cardView: { ... }
    $(document).on('preInit.dt.dtcv', function (e, settings) {
        if (e.namespace !== 'dt') {
            return;
        }

        var init = settings.oInit.cardView;
        var defaults = DataTable.defaults.cardView;

        if (init || defaults) {
            var options = $.extend(true, {}, defaults, typeof init === 'object' ? init : {});
            new CardView(settings, options);
        }
    });

    return CardView;
}));
