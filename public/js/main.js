window.addEventListener('DOMContentLoaded', init);

const store = {};

function init() {

    "use strict";

    try {
        checkStore();

        const googleUrl = document.getElementById('google');
        const pos = googleUrl.src.indexOf('=');
        store.recaptchaKey = googleUrl.src.slice(pos + 1);

        const options = [

            {url: '../public/js/initReview.js', func: () => initReview()},
            {url: '../public/js/initAnalytic.js', func: () => initAnalytic()},
            {url: '../public/js/initForm.js', func: () => initForm()},
        ];

        for (let option of options) {
            
            attachScriptToHead(option.url, option.func);
        }
    } catch (error) {
        console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
    }
}

function loadError(onError) {
    throw new Error("The script " + onError.target.src + " didn't load correctly.");
}

function attachScriptToHead(url, onloadFunction) {

    let newScript = document.createElement('script');
    newScript.src = url;
    newScript.onerror = loadError;

    if (onloadFunction) { newScript.onload = onloadFunction; }

    document.head.append(newScript);
}

async function fetchJson(url, data = {}) {

    const options = prepareOptions(data);

    let response = await fetch(url, options);

    if (!response.headers.get('Content-Type').startsWith('application/json')) {

        throw new ResponseError('response-header is wrong!');
    }

    return await response.json();
}

function prepareOptions(data) {

    let headers = {'X-Requested-With': 'fetch'};
    let options = {
        method: 'GET',
        mode: 'same-origin', //'cors'
        cache: 'no-store',
        credentials: 'same-origin',
        redirect: 'error', 
        referrerPolicy: 'origin-when-cross-origin', //'no-referrer',
    };

    if ('headers' in data) {
        headers = Object.assign(headers, data.headers);
    }

    options = Object.assign(options, data);
    options.headers = headers;
    return options;
}

/**
 * Checking object if it is empty
 * @param obj
 * @returns {boolean}
 */
function isEmpty(obj) {

    for (let prop in obj) {
        // Object is empty
        return false;
    }

    return true;
}

function createElem(obj) {

    const elem = document.createElement(obj.tag);

    if ('text' in obj) {
        elem.textContent = obj.text;
    }

    if ('id' in obj) {
        elem.id = obj.id;
    }

    if ('class' in obj) {
        elem.className = obj.class;
    }

    return elem;
}

function checkResponse(response) {

    if (typeof(response) !== 'object' || response == null) {
        throw new ResponseError('missing-response');
    }

    if ('success' in response && 'data' in response && response.success === true) {
        return response;
    }

    if ('success' in response && 'error' in response && response.success === false) {
        return response;
    }

    throw new ResponseError('invalid-response');
}

function getData(response) {

    response = checkResponse(response);

    if (!response.success) {
        throw new ReviewError(response.error);
    }

    return response.data;
}

function checkStore() {

    if (typeof(store) !== 'object' || store == null) {
        throw new StoreError('missing-store or store is not object');
    }

    return true;
}

function showResponse(success, message) {

    let elemData = {
        tag: 'div',
        id: success ? 'success' : 'error',
        text: message,
    };
    // create element to show response from server
    const elemForResponse = createElem(elemData);
    document.body.prepend(elemForResponse);

    elemForResponse.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });

    elemForResponse.style.opacity = 1;

    setTimeout(function() {
        elemForResponse.style.opacity = 0;
    }, 2500);

    setTimeout(() => elemForResponse.remove(), 3500);
}

function ResponseError(message) {
    this.message = message;
    this.name = "ResponseError";
    this.lineNumber = (new Error).stack.split("\n")[1];
}

function StoreError(message) {
    this.message = message;
    this.name = "StoreError";
    this.lineNumber = (new Error).stack.split("\n")[1];
}

function ReviewError(error) {
    this.message = error.message ? error.message : error;
    this.validation = error.validation ? error.validation : false;
    this.name = "ReviewError";
    this.lineNumber = (new Error).stack.split("\n")[1];
}
