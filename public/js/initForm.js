const URI_ADDITION_REVIEW = 'api/v1/reviews';
const TAG_FOR_ERROR_BOX = 'p';
const CLASS_INVALID = 'invalid';
const CLASS_FOCUS = 'valid';
// const ID_BOX_FILE = 'box-file';
// const ID_FILE_NAME = 'file-name';
// const ID_CANCEL = 'cancel';

const MAX_NAME_LENGTH = 40;
const MAX_TEXT_LENGTH = 650;
const MAX_FILE_SIZE = 2 * 1024 * 1024;

const EMPTY_FIELD = 'Поле обязательное для заполнения!';
const EMPTY_THEME = 'Выберите тему!';
const INVALID_NAME = 'Недопустимое имя!';
const INVALID_NAME_LENGTH = 'Имя не может быть более 40 символов!';
const INVALID_TEXT_LENGTH = 'Сообщение не может быть более 650 символов!';
const INVALID_FILE_SIZE = 'Файл может быть не более 2 МБ!';


function initForm() {

    const form = document.forms['form-review'];
    // Получаем массив елементов формы и удаляем последний элемент button
    // const formElements = Array.from(form.elements);
    const formElements = [...form.elements];
    formElements.pop();

    store.form = {
        collectionFormElements: form.elements,
    };

    // Добавления событий к элементам формы.
    // Проверка введеных пользователем данных при смене фокуса.
    formElements.forEach(formElement => {

        formElement.value = '';

        if (formElement.matches('#image')) {

            formElement.addEventListener('change', () => {
                cancelError(formElement);
                showFileName(formElement);
                showCancelButton(formElement);
            });
        } else {
            formElement.addEventListener('focusin', getFocus);
            formElement.addEventListener('focusout', moveFocus);
        }
    });

    // Добавление события к кнопке отпраки данных на сервер
    form.addEventListener('submit', function(event) {

        event.preventDefault();

        let validationMessages = validateForm(formElements);

        if (validationMessages.length !== 0) {
            return false;
        }

        const formData = new FormData(form);

        grecaptcha.ready(() => {
            grecaptcha.execute(store.recaptchaKey, {action: 'submit'})
                .then(token => {
                    formData.append('token', token);

                    const options = {
                        method: 'POST',
                        body: formData,
                    };

                    fetchJson(URI_ADDITION_REVIEW, options)
                        .then(response => getData(response))
                        .then(data => { showResponse(true, data.message); clearForm(formElements); })
                        .catch(error => handleError(error));
                });
        });
    });
}

function handleError(error) {

    if (error instanceof ReviewError) {

        if (!error.validation) {
            showResponse(false, error.message);
            console.error(error.message);
            return true;
        }

        const validations = error.validation;

        for (let key in validations) {
            
            if (!store.form.collectionFormElements.hasOwnProperty(key)) {
                console.error('Wrong key in error.validation!');
                return false;
            }
            
            let formElement = store.form.collectionFormElements[key];
            
            if (formElement.classList.contains(CLASS_INVALID)) {
                formElement.nextElementSibling.textContent = validations[key];
            } else {
                showWarning(formElement, validations[key]);
            }
        }
        return true;
    }

    console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
}

// Фокусировка на элементе
function getFocus() {

    // Задание начального вида элемента "input"
    this.classList.add(CLASS_FOCUS);

    let error = this.classList.contains(CLASS_INVALID);

    if (error) {
        this.addEventListener('input', cancelError(this), {once: true});
    } 
}

// Перемещение фокуса
function moveFocus() {

    this.value = this.value.trim();
    // Валидация значения введенного в поле формы при перемещении фокуса
    let validationMessage = validateField(this);

    if (validationMessage) {
        showWarning(this, validationMessage);
    } else {
        this.classList.remove(CLASS_FOCUS);
    }
}

// Добавление на страницу сообщения об ошибке
function showWarning(obj, validationMessage)
{
    let error = obj.classList.contains(CLASS_INVALID);

    if (!error) {
        obj.classList.add(CLASS_INVALID);
        obj.classList.remove(CLASS_FOCUS);

        let elemData = {
            tag: TAG_FOR_ERROR_BOX,
            class: 'error-box',
            text: validationMessage,
        };
        const errorBox = createElem(elemData);
        obj.after(errorBox);
    } 
}

function cancelError(formElement) {

    let error = formElement.classList.contains(CLASS_INVALID);

    if (error) {
        formElement.classList.remove(CLASS_INVALID);
        formElement.nextElementSibling.remove();
    }
}

function validateForm(formElements) {

    const validationMessages = [];

    formElements.forEach(formElement => {

        let validationMessage = validateField(formElement);

        if (validationMessage) {
            showWarning(formElement, validationMessage);
            validationMessages.push(validationMessage);
        } 
    });

    return validationMessages;
}

function validateField(obj)
{
    let $validationMessage = '';

    switch (obj.name) {
        case 'name':
            if (isEmpty(obj.value)) {
                $validationMessage = EMPTY_FIELD;
            } else if (!isValidUserName(obj.value)) {
                $validationMessage = INVALID_NAME;
            } else if (!isValidLength(obj.value, MAX_NAME_LENGTH)) {
                $validationMessage = INVALID_NAME_LENGTH;
            }
            break;

        case 'theme':
            if (isEmpty(obj.value)) {
                $validationMessage = EMPTY_THEME;
            } 
            break;

        case 'text':
            if (isEmpty(obj.value)) {
                $validationMessage = EMPTY_FIELD;
            } else if (!isValidLength(obj.value, MAX_TEXT_LENGTH)) {
                $validationMessage = INVALID_TEXT_LENGTH;
            }
            break;

        case 'image':
            if (obj.files.length) {
            
                if (!isAllowedFileSize(obj.files[0].size, MAX_FILE_SIZE)) {
                    $validationMessage = INVALID_FILE_SIZE;
                } else if (!isValidFileName(obj.files[0].name)) {
                    $validationMessage = INVALID_NAME;
                }
            }
            break;
    }
    return $validationMessage;
}

/**
 * @param userName
 * @returns {boolean}
 */
function isValidUserName(userName) {

    const regExp = /^(([A-Za-z]{1,2}`?[A-Za-z]{2,14})|([А-Яа-я]{3,14}))(((\s-\s)|\s|-){1}[A-Za-zА-Яа-я]+){0,2}$/u;
    return regExp.test(userName);
}

/**
 * @param fileName
 * @returns {boolean}
 */
function isValidFileName(fileName) {

    const regExp = /^([A-Za-z0-9_\-]{1,30}|[А-Яа-я0-9_\-]{1,30})\.[A-Za-z]{1,5}$/u;
    return regExp.test(fileName);
}

/**
 * @param value
 * @param maxLength
 * @returns {boolean}
 */
function isValidLength(value, maxLength) {

     return value.length < maxLength;
}

/**
 * If file < 2 Mb
 * @param fileSize
 * @param maxFileLength
 * @returns {boolean}
 */
function isAllowedFileSize(fileSize, maxFileLength) {
    
    return fileSize > maxFileLength;
}

function showFileName(formElement) {

    const idName = 'file-name';
    const fileBox = formElement.parentElement;
    let fileNameBox = fileBox.children[idName];

    if (fileNameBox) {
        fileNameBox.textContent = formElement.files[0].name;
    } else {
        let elemData = {
            tag: 'span',
            id: idName,
            text: formElement.files[0].name,
        };

        fileNameBox = createElem(elemData);
        fileBox.append(fileNameBox);
    }
}

function showCancelButton(formElement) {

    const fileBox = formElement.parentElement;
    const cancelElem = fileBox.children.cancel;

    if (!cancelElem) {

        const fileNameId = 'file-name';
        let elemData = {
            tag: 'span',
            id: 'cancel',
            text: 'Отменить',
        };

        const cancelElem = createElem(elemData);
        fileBox.append(cancelElem);

        cancelElem.addEventListener('click', function () {
            cancelError(formElement);
            fileBox.children[fileNameId].remove();
            cancelElem.remove();
            formElement.value = '';
        });
    }
}

function isValidation(error) {

    if (typeof(error) == 'object' && 'validation' in error) {
        return true;
    }

    return false;
}

function clearForm(formElements) {

    formElements.forEach(formElement => {
        formElement.value = '';
    });

    // Удаление из формы выбранного файла, если есть
    const fileNameBox = document.querySelector('#file-name');

    if (fileNameBox) {
        fileNameBox.nextElementSibling.remove();
        fileNameBox.remove();
    }
}