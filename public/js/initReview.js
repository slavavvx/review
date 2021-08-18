const URI_GETTING_REVIEW = 'api/v1/reviews';
const URI_LIKE = 'api/v1/likes';
const LIMIT_DEFAULT = 7;
const MAX_PAGINATED_PAGES = 5;
const REVIEW_ERROR = 'Произошла ошибка! Попробуйте позже.';
const NO_REVIEWS = '<h2>Отзывы:</h2><span>(0)</span><p>Нет отзывов на данный момент.</p>';

function initReview() {

    store.reviewElements = {
        wrapperReview: document.getElementById('wrapper-review'),
        wrapperPagination: document.getElementById('wrapper-pagination'),
        limit: document.getElementById('num-rows'),
        pagination: document.getElementById('pagination'),
    };

    store.requestReview = {
        limit: '?limit=',
        currentPage: '&currentPage=',
        pagination: `?limit=${LIMIT_DEFAULT}&currentPage=`,
    };

    store.requestLike = {
        options: {
            method: 'PUT',
            headers: {contentType: 'application/json; charset=UTF-8'},
        },
    };

    store.pagination = {};
    store.eventElem = '';

    try {
        // Добавление события к элементам like/dislike
        addEventToLike();
        // Добавление события к элементам кол-ва выводимых на странице отзывов
        addEventToLimit();
        // Добавление события к элементам пагинации
        addEventToPagination();
    } catch (error) {
        console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
    }
}

function addEventToLimit() {

    if (!store.reviewElements.limit) {
        throw new StoreError('missing-element');
    }

    const limitReviewsElems = store.reviewElements.limit.querySelectorAll('li ~ li');

    limitReviewsElems.forEach(limitElem => {
        limitElem.addEventListener('click', () => {
            
            try {
                changeLimit(limitElem);
            } catch (error) {
                console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
            }
        });
    });

    return true;
}

/**
 * @param limitElem It is element defining number rows per page
 * 
 */
function changeLimit(limitElem) {

    const className = 'focus-in';

    if (limitElem.classList.contains(className)) {
        return false;
    }

    if (!store.requestReview.limit || !store.requestReview.currentPage) {
        throw new StoreError('missing-element');
    }

    if (!store.reviewElements.wrapperReview || !store.reviewElements.wrapperPagination) {
        throw new StoreError('missing-element');
    }
    
    const limit = limitElem.textContent;
    const url = URI_GETTING_REVIEW + store.requestReview.limit + limit;

    fetchJson(url)
        .then(response => handleResponse(response));

    // Изменить стиль элемента на котором произошло событие
    showActiveElement(limitElem, className);
    // Для пагинации
    store.requestReview.pagination = store.requestReview.limit + limit + store.requestReview.currentPage;

    // Фиксация того что на элементах лимита произошло событие
    store.eventElem = limitElem.parentElement;
}

function addEventToPagination() {

    // стиль элемента пагинации при фокусе
    const className = 'page-focus';

    if (!store.reviewElements.pagination) {
        throw new StoreError('missing-element');
    }

    const pages = store.reviewElements.pagination.children;

    const paginatedPages = pages.length - 2;
    const totalRows = paginatedPages * LIMIT_DEFAULT;
    const totalPages = Math.ceil(totalRows / LIMIT_DEFAULT);
    
    store.pagination = {
        totalPages: totalPages,
        paginatedPages: paginatedPages,
    };

    for (let page of pages) {

        if (page.matches('#previous')) {
            page.addEventListener('click', () => handleArrowPrev(pages, className));
        } else if (page.matches('#next')) {
            page.addEventListener('click', () => handleArrowNext(pages, className));
        } else {
            page.addEventListener('click', () => handlePageNumber(page, pages, className));
        }
    }
}

/**
 * Обработка ответа сервера
 */
function handleResponse(response) {

    try {
        const data = getData(response);
        const review = displayReviews(data);

        if (!review) {
            hidePagination();
            return false;
        }

        addEventToLike();
        displayPagination(data);
        return true;
    } catch(error) {

        if (error instanceof ReviewError) {
            showResponse(false, REVIEW_ERROR);
        }

        console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
        return false;
    }
}

function displayReviews(data) {
    
    let reviews = checkResponseForReviews(data);
    const pagination = checkResponseForPagination(data);

     if (pagination.totalRows === 0) {
        store.reviewElements.wrapperReview.innerHTML = NO_REVIEWS;
        return false;
     }

    reviews = reviews.map(prepareReviewData).join('');
    store.reviewElements.wrapperReview.innerHTML = `<h2>Отзывы: </h2><span>(${pagination.totalRows})</span>` + reviews;
        
    return true;
}

function checkResponseForReviews(data) {

    if ('reviews' in data && typeof(data.reviews) == 'object') {

        return data.reviews;
    }

    throw new ResponseError('invalid-response for reviews');
}

function checkResponseForPagination(data) {

    if ('pagination' in data && typeof(data.pagination) == 'object') {

        if ('totalRows' in data.pagination && 'totalPages' in data.pagination && 'paginatedPages' in data.pagination) {
            return data.pagination;
        }
    }

    throw new ResponseError('invalid-response for pagination');
}

function prepareReviewData(obj) {

    let error = (value = 'Error') => {
        console.error('missing-properties for reviews');
        return value;
    };

    const outputData = {
        'review_id': ('review_id' in obj) ? obj.review_id : error(0),
        'username': ('username' in obj) ? obj.username : error(),
        'theme': ('theme' in obj) ? obj.theme : error(),
        'text': ('text' in obj) ? obj.text : error(),
        'image_name': ('image_name' in obj && obj.image_name != null) ? obj.image_name : 'default_user.jpg',
        'date': ('date' in obj) ? obj.date : error(),
        'like': ('like' in obj) ? obj.like : error(0),
        'dislike': ('dislike' in obj) ? obj.dislike : error(0),
    };

    return getHtmlSectionReviews(outputData);
}

function displayPagination(data) {

    const pagination = checkResponseForPagination(data);

    if (!store.eventElem) {
        throw new StoreError('missing-element');
    }

    const newPaginatedPages = pagination.paginatedPages;
    store.pagination.totalPages = pagination.totalPages;

    //Если событие произошло на элементах лимита то пагинация перерисовывается
    if (store.eventElem.matches('#num-rows')) {
        refreshPagination(newPaginatedPages);
    }

    store.pagination.paginatedPages = newPaginatedPages;

    return true;
}

function hidePagination() {

    if (store.reviewElements.pagination) {
        store.reviewElements.wrapperPagination.style.display = 'none';
    }
    return true;
}   

function refreshPagination(newPaginatedPages) {

    if (!store.pagination.paginatedPages || !store.reviewElements.pagination) {
        throw new StoreError('missing-element');
    }

    const currentlyPaginatedPages = store.pagination.paginatedPages;
    const pagesElem = store.reviewElements.pagination.children;
    let correction;

    if (currentlyPaginatedPages > newPaginatedPages) {
        correction = currentlyPaginatedPages - newPaginatedPages;

        for (let i = 0; i < correction; i++) {
            pagesElem.next.previousElementSibling.style.display = 'none';
        }
    } else {
        correction = newPaginatedPages - currentlyPaginatedPages;
        const lastPage = pagesElem[currentlyPaginatedPages];

        for (let i = 0; i < correction; i++) {

            if (lastPage.nextElementSibling.hasAttribute('style')) {
                lastPage.nextElementSibling.removeAttribute('style');
            }
        }
    }

    showActiveElement(pagesElem[1], 'page-focus');
    styleArrow(pagesElem, 1);

    return true;
}

function getHtmlSectionReviews(obj) {

    return `
        <div class="box-review">
            <div class="content">
                <div class="header">
                    <span>${obj.username}</span>
                    <span>${obj.date}</span>
                </div>
                <div class="text">
                    <figure>
                        <img src="../public/uploaded_images/${obj.image_name}" alt="Foto" width="96">
                    </figure>
                    <div>
                        <h3>${obj.theme}</h3>
                        <p>${obj.text}</p>
                    </div>
                </div>
            </div>
            <div class="like-box">
                <div>
                    <span>Был ли вам полезен отзыв?</span>
                </div>
                <div class="like">
                    <span>Да</span>
                    <span>${obj.like}</span>
                </div>
                <div class="dislike">
                    <span>Нет</span>
                    <span>${obj.dislike}</span>
                </div>
                <span hidden>${obj.review_id}</span>
            </div>
        </div>`;
}

function addEventToLike() {

    const likesElem = document.querySelectorAll('.like');
    const dislikesElem = document.querySelectorAll('.dislike');

    if (!likesElem || !dislikesElem) {
        throw new ReferenceError('no like or dislike elements');
    }

    likesElem.forEach(likeElem => {

        const reviewId = likeElem.parentElement.lastElementChild.textContent;

        likeElem.addEventListener('click', () => handleLike(reviewId, likeElem));
    });

    dislikesElem.forEach(dislikeElem => {

        const reviewId = dislikeElem.parentElement.lastElementChild.textContent;

        dislikeElem.addEventListener('click', () => handleLike(reviewId, dislikeElem));
    });

    return true;
}

function handleLike(reviewId, element) {

    if (!store.recaptchaKey) {
        throw new StoreError('no recaptchaKey');
    }

    const likeMessage = document.getElementById('like-message');

    if (likeMessage) {
        return false;
    }

    const data = prepareLikeData(element);

    if (!data) {
        throw new ReferenceError('missing-element for like');
    }

    data.reviewId = reviewId;

    grecaptcha.ready(() => {
        grecaptcha.execute(store.recaptchaKey, {action: 'like'})
            .then(token => {
                data.token = token;
                store.requestLike.options.body = JSON.stringify(data);

                fetchJson(URI_LIKE, store.requestLike.options)
                    .then(response => getData(response))
                    .then(data => displayLike(data, element))
                    .then(message => showMessageForLike(message))
                    .catch(error => {

                        if (error instanceof ReviewError) {
                            showMessageForLike(error.message);
                        }
                        console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
                    });
            });
    });
}

function prepareLikeData(element) {

    const className = 'liked';
    let data = {};

    if (element.matches('.like')) {
        
        if (element.classList.contains(className)) {
            data.like = 'ded';
        } else {
            data.like = 'add';
        }

        const dislikeElem = element.nextElementSibling;

        if (dislikeElem.classList.contains(className)) {
            dislikeElem.classList.remove(className);
            data.dislike = 'ded';
        }

        element.classList.toggle(className);
        return data;
    }

    if (element.matches('.dislike')) {
        
        if (element.classList.contains(className)) {
            data.dislike = 'ded';
        } else {
            data.dislike = 'add';
        }

        const likeElem = element.previousElementSibling;

        if (likeElem.classList.contains(className)) {
            likeElem.classList.remove(className);
            data.like = 'ded';
        }

        element.classList.toggle(className);
        return data;
    }  

    return false;
}

/**
 * Обработка ответа сервера при нажатии на like/dislike
 */
function displayLike(data, element) {

    data = checkResponseForLike(data);

    if (element.matches('.like')) {
        element.lastElementChild.textContent = data.like;
        const dislikeElem = element.nextElementSibling;
        dislikeElem.lastElementChild.textContent = data.dislike;
    } else if (element.matches('.dislike')) {
        element.lastElementChild.textContent = data.dislike;
        const likeElem = element.previousElementSibling;
        likeElem.lastElementChild.textContent = data.like;
    }

    return data.likeMessage;
}

function checkResponseForLike(data) {

    if ('like' in data && 'dislike' in data && 'likeMessage' in data) {
        return data;
    }

    throw new ResponseError('invalid-response for like');
}

function showMessageForLike(message) {

    const elemData = {
        tag: 'div',
        id: 'like-message',
        text: message,
    };

    const divElem = createElem(elemData);
    document.body.append(divElem);

    setTimeout(function() {
        divElem.style.bottom = 20 + 'px';
    }, 100);

    setTimeout(function() {
        divElem.removeAttribute('style');
    }, 2000);

    setTimeout(() => divElem.remove(), 2500);
}

function getHtmlSectionPagination(paginatedPages) {

    let htmlStr = '<li class="page-focus">1</li>';
    let nextElem = '<li id="next">&raquo;</li>';

    if (paginatedPages === 1) {
        nextElem = '<li id="next" class="disable-arrow">&raquo;</li>';
    }

    for (let i = 2; i <= paginatedPages; i++) {
        
        htmlStr += `<li>${i}</li>`;
    }

    return `
        <li id="previous" class="disable-arrow">&laquo;</li>${htmlStr + nextElem}
    `;
}

/**
 * @param page It is element defining page number
 * @param pages All elements defining page numbers
 * @param className
 * @returns {boolean}
 */
function handlePageNumber(page, pages, className) {

    if (!store.pagination.totalPages || !store.requestReview.pagination) {
        throw new StoreError('missing-element');
    }

    if (page.classList.contains(className)) {
        return false;
    }

    const totalPages = store.pagination.totalPages;

    // Фиксация того что на элементах пагинации произошло событие
    store.eventElem = page.parentElement;

    let currentPage = page.textContent;
    const url = URI_GETTING_REVIEW + store.requestReview.pagination + currentPage;

    fetchJson(url)
        .then(response => handleResponse(response));
            
    // Подключение массива чисел для перенумерации страниц, так как выводятся только пять номеров
    if (totalPages > MAX_PAGINATED_PAGES) {
        let numbersForAction = getNumbersForAction();

        if (numbersForAction.includes(currentPage) && pages[1].textContent !== currentPage) {
            let numPage = currentPage;

            for (let i = 1; i <= MAX_PAGINATED_PAGES; i++) {
                pages[i].textContent = numPage;
                numPage++;
            }
            showActiveElement(pages[1], className);
        }
    }
    
    showActiveElement(page, className);
    styleArrow(pages, currentPage);
    return true;
}

function handleArrowPrev(pages, className) {

    if (!store.reviewElements.pagination || !store.requestReview.pagination) {
        throw new StoreError('missing-element');
    }

    if (pages.previous.classList.contains('disable-arrow')) {
        return false;
     }

     // Фиксация того что на элементах пагинации произошло событие
    store.eventElem = pages.previous.parentElement;

    // Определяется где до клика на элементе находился фокус
    const focus = store.reviewElements.pagination.querySelector('.' + className);
    let currentPage = focus.textContent;

    currentPage--;
    const url = URI_GETTING_REVIEW + store.requestReview.pagination + currentPage;

    fetchJson(url)
        .then(response => handleResponse(response));
   
    // Если первый элемент пагинации находится в фокусе и это не первая страница производится перенумерация страниц
    // иначе фокус получает предыдущая страница
    if (pages[1].classList.contains(className)) {
        let j = currentPage;
        
        for (let i = 1; i <= MAX_PAGINATED_PAGES; i++) {
            pages[i].textContent = j;
            j++;
        }
    } else {
        focus.classList.remove(className);
        focus.previousElementSibling.classList.add(className);
    }

    styleArrow(pages, currentPage);
    return true;
}

function handleArrowNext(pages, className) {

    if (!store.pagination.paginatedPages || !store.reviewElements.pagination || !store.requestReview.pagination) {
        throw new StoreError('missing-element');
    }

    if (pages.next.classList.contains('disable-arrow')) {
        return false;
    }

    const paginatedPages = store.pagination.paginatedPages;

    // Фиксация того что на элементах пагинации произошло событие
    store.eventElem = pages.next.parentElement;

    // Определяется где до клика по элементу находился фокус
    const focus = store.reviewElements.pagination.querySelector('.' + className);
    let currentPage = focus.textContent;

    currentPage++;
    const url = URI_GETTING_REVIEW + store.requestReview.pagination + currentPage;

    fetchJson(url)
        .then(response => handleResponse(response));

    // Если фокус находится на последнем элементе пагинации, a текущая страница не последняя,
    // то производится перенумерация страниц
    if (pages[paginatedPages].classList.contains(className)) {
        let j = currentPage;

        for (let i = paginatedPages; i >= 1; i--) {
            pages[i].textContent = j;
            j--;
        }
    } else {
        focus.classList.remove(className);
        focus.nextElementSibling.classList.add(className);
    }
    
    styleArrow(pages, currentPage);
    return true;
}

function showActiveElement(element, className) {

    //Если какой-то элемент уже в фокусе, т.е содержит данный класс, то удалить этот класс
    let anyElem = document.querySelector('.' + className);

    if (anyElem) {
        anyElem.classList.remove(className);
    }
    // Добавить фокус выбранному элементу
    element.classList.add(className);
}

function styleArrow(pages, currentPage) {

    const className = 'disable-arrow';
    const totalPages = store.pagination.totalPages;

    if (!totalPages) {
        console.error('invalid-data for style arrow');
        pages.previous.classList.add(className);
        pages.next.classList.add(className);
        return false;
    }
    
    if (currentPage == 1) {
        pages.previous.classList.add(className);
    } else if (pages.previous.classList.contains(className)) {
        pages.previous.classList.remove(className);
    }


    if (currentPage == totalPages) {
        pages.next.classList.add(className);
    } else if (pages.next.classList.contains(className)) {
        pages.next.classList.remove(className);
    }

    return true;
}

// Получение массива цифр для сдвига пронумерованных страниц,
// чтобы постоянно отображалось пять страниц
function getNumbersForAction() {
    
    if (!store.pagination.totalPages) {
        throw new StoreError('missing-element');
    }

    let numberForAction = 0;
    let numbersForAction = [];
    let totalPages = store.pagination.totalPages;

    if (totalPages > MAX_PAGINATED_PAGES) {
        let numberCycle = totalPages / MAX_PAGINATED_PAGES;
        
        if (numberCycle < 2) {
            numberForAction = totalPages - (MAX_PAGINATED_PAGES - 1);
            numbersForAction.push(numberForAction);
        }

        if (numberCycle >= 2) {
            let numberLastPage = MAX_PAGINATED_PAGES;

            while (numberLastPage <= totalPages) {
                numberForAction = numberLastPage;
                numbersForAction.push(numberForAction);

                let restPages = totalPages - numberLastPage;
                let increment = (restPages + 1) - MAX_PAGINATED_PAGES;

                if (increment < MAX_PAGINATED_PAGES) {
                    numberForAction = numberLastPage + increment;
                    numbersForAction.push(numberForAction);
                    break;
                } 
                numberLastPage += (MAX_PAGINATED_PAGES - 1);
            }
        }
    }
    return numbersForAction;
}