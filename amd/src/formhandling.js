// This file is part of FLIP Plugins for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * JavaScript for the add partnerships form.
 *
 * @module     local_equipment/formhandling
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';
import { init as mobileCourseSelect } from './mobile_course_select';
/**
 * Initialize the add partnerships form functionality.
 */
export const init = () => {
    Log.debug('init function called in formhandling.js');
};

/**
 * Set up partnerships handling.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 */
export const setupStudentsHandling = (name, type) => {
    setupFieldsetNameUpdates(name, type);
    Log.debug('Setup student handling javascript initialized');
    Log.debug(name);
    Log.debug(`${name}${type}`);
    const selector = `fieldset[id^='id_${name}${type}_']`;

    $(document).on('click', `.local-equipment-remove-${name}`, function () {
        const $fieldset = $(this).closest(selector);
        $fieldset.remove();
        updateFieldsetNumbers(name, type);
        updateHiddenFields(name, type, true);
        renumberFormElements(name, type);
        updateTrashIcons(name, type);
    });

    updateTrashIcons(name, type);
};

/**
 * Update the unique number of the fieldset.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 */
const updateFieldsetNumbers = (name, type) => {
    Log.debug(`${name} ${type}`);
    $(`.local-equipment-${name}-${type}`).each((index, element) => {
        getString(name, 'local_equipment', index + 1)
            .then((string) => {
                $(element).text(string);
            })
            .catch((error) => {
                Log.error(`Error updating ${name} ${type}: `, error);
            });
    });
};

/**
 * Update hidden fields.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 * @param {boolean} usePlural Whether or not the 'name' input field should be pluralized.
 */
const updateHiddenFields = (name, type, usePlural = false) => {
    const inputName = usePlural ? name + 's' : name;
    const fieldsetsCount = $(`fieldset[id^='id_${name}${type}_']`).length;
    $(`input[name="${inputName}"]`).val(fieldsetsCount);
    Log.debug(`Updated ${inputName} to ${fieldsetsCount}`);

    // Update the URL if necessary
    const url = new URL(window.location.href);
    url.searchParams.set('repeatno', fieldsetsCount);
    window.history.replaceState({}, '', url);
};

/**
 * Renumber form elements.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 */
const renumberFormElements = (name, type) => {
    $(`fieldset[id^='id_${name}${type}_']`).each((index, fieldset) => {
        $(fieldset)
            .find('input, select, textarea')
            .each((_, element) => {
                const name = $(element).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    $(element).attr('name', newName);
                }
                const id = $(element).attr('id');
                if (id) {
                    const newId = id.replace(/_\d+_/, `_${index}_`);
                    $(element).attr('id', newId);
                }
            });
    });
};

/**
 * Update trash icons visibility.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 */
const updateTrashIcons = (name, type) => {
    const fieldsets = $(`fieldset[id^='id_${name}${type}_']`);
    if (fieldsets.length > 1) {
        $(`.local-equipment-remove-${name}`).show();
    } else {
        $(`.local-equipment-remove-${name}`).hide();
    }
};

/**
 * Enhance multi-select fields to allow selection without CTRL key.
 */
const enhanceMultiSelects = () => {
    Log.debug('Enhancing multi-select fields');
    $('select[multiple]').each((index, element) => {
        $(element)
            .on('mousedown', (e) => {
                e.preventDefault();
                const option = $(e.target).closest('option');
                if (option.length) {
                    option.prop('selected', !option.prop('selected'));
                    $(element).trigger('change');
                }
            })
            .on('mousemove', (e) => {
                e.preventDefault();
            });
    });
};

/**
 * Set up real-time header updates for student names.
 * @param {string} name The name of the fieldset.
 * @param {string} type The type of element.
 */
export const setupFieldsetNameUpdates = (name, type) => {
    const updateFieldsetHeader = (fieldset) => {
        const index = parseInt(fieldset.id.split('_').pop(), 10);
        const firstNameInput = fieldset.querySelector(
            `#id_${name}_firstname_${index}`
        );
        const headerField = fieldset.querySelector(`#id_${name}name_${index}`);
        const header = fieldset.querySelector('h3');

        if (headerField && header) {
            const updateHeader = () => {
                const headerName = headerField.value.trim();
                if (headerName) {
                    // Swap the two next lines if you want only the name to show with no description.
                    // getString('a', 'local_equipment', headerName)
                    getString(name + type, 'local_equipment', headerName)
                        .then((str) => {
                            header.textContent = str;
                        })
                        .catch((error) => {
                            // eslint-disable-next-line no-console
                            console.error(
                                'Error updating student header:',
                                error
                            );
                            header.textContent = `Student ${index + 1}`;
                        });
                } else {
                    getString(name, 'local_equipment', index + 1)
                        .then((str) => {
                            header.textContent = str;
                        })
                        .catch((error) => {
                            // eslint-disable-next-line no-console
                            console.error(
                                'Error updating student header:',
                                error
                            );
                            header.textContent = `Student ${index + 1}`;
                        });
                }
            };

            headerField.addEventListener('input', updateHeader);
            // Trigger initial update
            updateHeader();
        }
        if (firstNameInput && header) {
            const updateHeader = () => {
                const headerName = firstNameInput.value.trim();
                if (headerName) {
                    // Swap the two next lines if you want only the name to show with no description.
                    // getString('a', 'local_equipment', headerName)
                    getString(name + type, 'local_equipment', headerName)
                        .then((str) => {
                            header.textContent = str;
                        })
                        .catch((error) => {
                            // eslint-disable-next-line no-console
                            console.error(
                                'Error updating student header:',
                                error
                            );
                            header.textContent = `Student ${index + 1}`;
                        });
                } else {
                    getString(name, 'local_equipment', index + 1)
                        .then((str) => {
                            header.textContent = str;
                        })
                        .catch((error) => {
                            // eslint-disable-next-line no-console
                            console.error(
                                'Error updating student header:',
                                error
                            );
                            header.textContent = `Student ${index + 1}`;
                        });
                }
            };

            firstNameInput.addEventListener('input', updateHeader);
            // Trigger initial update
            updateHeader();
        }
    };

    const setupFieldset = (fieldset) => {
        updateFieldsetHeader(fieldset);
    };

    // Initial setup for existing student fields
    document
        .querySelectorAll(`fieldset[id^="id_${name}${type}_"]`)
        .forEach(setupFieldset);

    // Setup for dynamically added student fields
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach((node) => {
                    if (
                        node.nodeType === Node.ELEMENT_NODE &&
                        node.matches(`fieldset[id^="id_${name}${type}_"]`)
                    ) {
                        setupFieldset(node);
                    }
                });
            }
        });
    });

    observer.observe(document.querySelector('form'), {
        childList: true,
        subtree: true,
    });
};

export const setupMultiSelects = () => {
    const isMobile = () => {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
            navigator.userAgent
        );
    };
    if (isMobile()) {
        mobileCourseSelect();
    } else {
        enhanceMultiSelects();
    }
};

export const updateURLId = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const idParam = urlParams.get('id');
    const id = $('input[name="id"]').val();

    Log.debug(`urlParams: ${urlParams}`);
    Log.debug(`id: ${id}`);
    Log.debug(`window.location.href: ${window.location.href}`);
    Log.debug(
        `window.location.href.includes('id='): ${window.location.href.includes(
            'id='
        )}`
    );

    if (idParam === null) {
        const { href } = window.location;
        const separator = href.includes('?') ? '&' : '?';
        const newUrl = `${href}${separator}id=${id}`;
        history.pushState({}, '', newUrl);
    }
};
