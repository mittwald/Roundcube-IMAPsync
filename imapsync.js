if (window.rcmail) {
    rcmail.addEventListener('init', function () {
        var form = document.getElementById('imapsync-form'),
            submitButton = document.getElementById('imapsync-submit'),
            verifyButton = document.getElementById('imapsync-verify'),
            cancelButton = document.getElementById('imapsync-cancel'),
            startRequest = null,
            preflightRequest = null,
            lastPreflightReady = false;

        function label(name) {
            return rcmail.gettext(name, 'imapsync');
        }

        function setFormDisabled(disabled) {
            if (submitButton) {
                submitButton.disabled = disabled || !lastPreflightReady;
                submitButton.classList.toggle('disabled', submitButton.disabled);
            }

            if (verifyButton) {
                verifyButton.disabled = disabled;
            }

            if (cancelButton) {
                cancelButton.hidden = !disabled;
            }
        }

        function disableSubmit() {
            lastPreflightReady = false;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
            }
        }

        function setSubmitFromPreflight(result) {
            lastPreflightReady = !!result.readyToStart;

            if (submitButton) {
                submitButton.disabled = !lastPreflightReady;
                submitButton.classList.toggle('disabled', !lastPreflightReady);
            }
        }

        function renderProgress(progress) {
            var region = document.getElementById('imapsync-progress'),
                list = document.getElementById('imapsync-progress-list');

            if (!region || !list) {
                return;
            }

            list.innerHTML = '';
            progress.forEach(function (item) {
                var row = document.createElement('div'),
                    folder = document.createElement('span'),
                    counter = document.createElement('span');

                row.className = 'imapsync-progress-row';
                folder.className = 'imapsync-progress-folder';
                counter.className = 'imapsync-progress-counter';
                folder.textContent = item.folder;
                counter.textContent = item.current + '/' + item.total;
                row.appendChild(folder);
                row.appendChild(counter);
                list.appendChild(row);
            });

            region.hidden = progress.length === 0;
        }

        function renderResult(result) {
            var region = document.getElementById('imapsync-result'),
                content = document.getElementById('imapsync-result-content'),
                table = document.createElement('table'),
                errors = result.errors || [],
                messageKey = result.fatalError || errors.length ? 'donewitherrors' : 'donesuccess';

            if (!region || !content) {
                return;
            }

            table.className = 'imapsync-summary-table';
            content.innerHTML = '';
            summaryRow(label('folderssynced'), result.foldersSynced || 0, table);
            summaryRow(label('messagescopied'), result.messagesCopied || 0, table);
            summaryRow(label('messagesskipped'), result.messagesSkipped || 0, table);
            summaryRow(label('errors'), errors.length + (result.fatalError ? 1 : 0), table);
            content.appendChild(table);

            if (result.fatalError || errors.length) {
                content.appendChild(errorList(result.fatalError, errors, result.quotaExceeded));
            }

            region.hidden = false;
            rcmail.display_message(label(messageKey), result.fatalError || errors.length ? 'warning' : 'confirmation');
        }

        function renderPreflight(result) {
            var region = document.getElementById('imapsync-preflight'),
                content = document.getElementById('imapsync-preflight-content'),
                list = document.createElement('ul'),
                freeBytes = result.destinationLimit !== null && result.destinationLimit !== undefined
                    && result.destinationUsed !== null && result.destinationUsed !== undefined
                    ? Math.max(0, result.destinationLimit - result.destinationUsed)
                    : null,
                folderDetail = fillPlaceholders(label('preflightfoldersdetail'), {
                    count: result.folderCount || 0
                }),
                quotaDetail;

            if (!region || !content) {
                return;
            }

            if (result.quotaChecked && result.quotaFits) {
                quotaDetail = fillPlaceholders(label('preflightquotaokdetail'), {
                    source: formatBytes(result.sourceBytes),
                    free: formatBytes(freeBytes)
                });
            } else if (result.quotaChecked) {
                quotaDetail = fillPlaceholders(label('preflightquotafaildetail'), {
                    source: formatBytes(result.sourceBytes),
                    free: formatBytes(freeBytes)
                });
            } else {
                quotaDetail = label('preflightquotaunknowndetail');
            }

            list.className = 'imapsync-checklist';
            list.appendChild(checkRow(
                result.connectionOk ? 'ok' : 'fail',
                label('preflightcheckconnection'),
                result.connectionOk ? '' : result.connectionError
            ));
            list.appendChild(checkRow(
                result.foldersOk && result.folderCount > 0 ? 'ok' : 'fail',
                label('preflightcheckfolders'),
                folderDetail
            ));
            list.appendChild(checkRow(
                result.quotaChecked ? (result.quotaFits ? 'ok' : 'fail') : 'warn',
                label('preflightcheckquota'),
                quotaDetail
            ));
            if (result.timeoutRisk) {
                list.appendChild(checkRow(
                    'warn',
                    '',
                    fillPlaceholders(label('preflighttimeoutwarn'), {
                        source: formatBytes(result.sourceBytes),
                        seconds: result.maxExecutionTime
                    })
                ));
            }

            content.innerHTML = '';
            content.appendChild(list);
            content.appendChild(preflightHint(result.readyToStart));
            region.hidden = false;
            setSubmitFromPreflight(result);
        }

        function checkRow(status, title, detail) {
            var item = document.createElement('li'),
                icon = document.createElement('span'),
                text = document.createElement('span'),
                titleElement = document.createElement('span'),
                detailElement = document.createElement('span'),
                iconText = status === 'ok' ? '✓' : (status === 'warn' ? '⚠' : '✗');

            item.className = 'imapsync-check-row';
            icon.className = 'imapsync-check-icon imapsync-check-' + status;
            icon.textContent = iconText;
            titleElement.textContent = title;
            text.appendChild(titleElement);

            if (detail) {
                detailElement.className = 'imapsync-check-detail';
                detailElement.textContent = detail;
                text.appendChild(detailElement);
            }

            item.appendChild(icon);
            item.appendChild(text);

            return item;
        }

        function preflightHint(readyToStart) {
            var hint = document.createElement('p');

            hint.className = 'imapsync-preflight-hint';
            hint.textContent = label(readyToStart ? 'preflightreadyhint' : 'preflightnotreadyhint');

            return hint;
        }

        function formatBytes(bytes) {
            var units = ['B', 'KB', 'MB', 'GB', 'TB'],
                i = 0,
                n = Math.max(0, bytes);

            if (bytes === null || bytes === undefined) {
                return '?';
            }

            while (n >= 1024 && i < units.length - 1) {
                n /= 1024;
                i++;
            }

            return (i === 0 ? n.toFixed(0) : n.toFixed(1)) + ' ' + units[i];
        }

        function fillPlaceholders(text, values) {
            Object.keys(values).forEach(function (key) {
                text = text.split('%' + key + '%').join(values[key]);
            });

            return text;
        }

        function summaryRow(title, value, table) {
            var row = document.createElement('tr'),
                header = document.createElement('th'),
                cell = document.createElement('td');

            header.textContent = title;
            cell.textContent = value;
            row.appendChild(header);
            row.appendChild(cell);
            table.appendChild(row);
        }

        function errorList(fatalError, errors, quotaExceeded) {
            var list = document.createElement('ul');

            list.className = 'imapsync-errors';
            if (quotaExceeded) {
                list.appendChild(errorItem(label('errorquota')));
            } else if (fatalError) {
                list.appendChild(errorItem(fatalError));
            }

            errors.forEach(function (error) {
                list.appendChild(errorItem(error));
            });

            return list;
        }

        function errorItem(error) {
            var item = document.createElement('li');

            item.textContent = error;

            return item;
        }

        rcmail.addEventListener('plugin.imapsync_status', function (payload) {
            renderProgress(payload.progress || []);

            if (payload.result) {
                startRequest = null;
                setFormDisabled(false);
                renderResult(payload.result);
            } else if (payload.running) {
                setFormDisabled(true);
            }
        });

        rcmail.addEventListener('plugin.imapsync_preflight', function (result) {
            preflightRequest = null;

            if (verifyButton) {
                verifyButton.disabled = false;
            }

            renderPreflight(result);
        });

        rcmail.addEventListener('plugin.imapsync_error', function (message) {
            startRequest = null;
            preflightRequest = null;
            setFormDisabled(false);
            rcmail.display_message(message || label('errorvalidation'), 'error');
        });

        if (cancelButton) {
            cancelButton.addEventListener('click', function () {
                if (startRequest && startRequest.abort) {
                    startRequest.abort();
                }

                startRequest = null;
                setFormDisabled(false);
            });
        }

        if (verifyButton && form) {
            verifyButton.addEventListener('click', function () {
                var lock,
                    resultRegion = document.getElementById('imapsync-result'),
                    progressRegion = document.getElementById('imapsync-progress');

                if (resultRegion) {
                    resultRegion.hidden = true;
                }

                if (progressRegion) {
                    progressRegion.hidden = true;
                }

                disableSubmit();
                verifyButton.disabled = true;
                lock = rcmail.set_busy(true, 'loading');
                preflightRequest = rcmail.http_post('plugin.imapsync.preflight', $(form).serialize(), lock);
            });
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                var lock;

                event.preventDefault();
                if (!lastPreflightReady) {
                    disableSubmit();

                    return;
                }

                if (!window.confirm(label('confirmstart'))) {
                    return;
                }

                document.getElementById('imapsync-result').hidden = true;
                setFormDisabled(true);
                lock = rcmail.set_busy(true, 'loading');
                startRequest = rcmail.http_post('plugin.imapsync.start', $(form).serialize(), lock);
            });
        }

        ['imapsync-host', 'imapsync-port', 'imapsync-encryption', 'imapsync-user', 'imapsync-password'].forEach(function (id) {
            var element = document.getElementById(id);

            if (element) {
                element.addEventListener('input', disableSubmit);
                element.addEventListener('change', disableSubmit);
            }
        });
    });
}
