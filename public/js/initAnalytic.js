const URI_ANALYTICS = 'api/v1/analytics';

function initAnalytic() {

    let wrapperElem = document.querySelector('.wrapper');

    const analyticsElem = createAnalyticsElem();
    wrapperElem.append(analyticsElem);

    store.analyticsElements = {
        contentElem: analyticsElem.querySelector('.a-content'),
    };

	analyticsElem.addEventListener('click', () => {
	 	
	 	try {
	 		handleAnalytics();
	 	} catch (error) {
    		console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
	 	}
	});
}

function createAnalyticsElem() {

    let analyticsElem = document.createElement('div');
    let headerElem = document.createElement('h3');
    let contentElem = document.createElement('div');

    analyticsElem.className = 'analytics';
    contentElem.className = 'a-content';
    headerElem.textContent = 'Аналитика';

    analyticsElem.append(headerElem);
    analyticsElem.append(contentElem);

    return analyticsElem;
}

function handleAnalytics() {

	if (!store.analyticsElements.contentElem) {
        throw new StoreError('missing-element');
    }

	const contentElem = store.analyticsElements.contentElem;

	if (contentElem.hasAttribute('style')) {
		contentElem.removeAttribute('style');
	} else {
		fetchJson(URI_ANALYTICS)
	        .then(response => getData(response))
	        .then(data => createContent(data))
	        .then(title => displayAnalytics(title))
	        .catch(error => {

	        	if (error instanceof ReviewError) {
    				contentElem.innerHTML = `<p>${error.message}</p>`;
        			displayAnalytics();
    			}
        		console.error(error.name + ': ' + error.message + ': ' + error.lineNumber);
	        });
	}
}

function checkResponseForAnalytics(data) {

    if (typeof(data.analytics) == 'object' && 'analytics' in data) {

    	if ('totalRows' in data.analytics && 'title' in data.analytics && 'description' in data.analytics) {
            return data.analytics;
        }
    }

    throw new ResponseError('invalid-response for analytics');
}

function createContent(data) {

	if (!store.analyticsElements.contentElem) {
        throw new StoreError('missing-element');
    }

	const analytics = checkResponseForAnalytics(data);
	const contentElem = store.analyticsElements.contentElem;

	if (analytics.totalRows === 0) {
		contentElem.innerHTML = `<p>${analytics.title}</p>`;
	} else {
		contentElem.innerHTML = getContentHTML(analytics);
	}

	return analytics.title;
}

function displayAnalytics(title = '') {

	if (!store.analyticsElements.contentElem) {
        throw new StoreError('missing-element');
    }

	const styles = {
		love:    'Клиенты нас любят!',
		improve: 'Нам надо совершенствоваться!',
		change:  'Пора меняться!',
		hate:    'Надо сжечь это место!',
	};

	const contentElem = store.analyticsElements.contentElem;
	const titleElem = contentElem.querySelector('p');

	for (let style in styles) {

		if (styles[style] === title) {
			titleElem.id = style;
		} 
	}

    contentElem.style.maxHeight = contentElem.scrollHeight + 'px';
    contentElem.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}

function getContentHTML(obj) {

	return `
		<p>${obj.title}</p>
		<ul>
			${obj.description.map(item => {
				return `<li>${item}</li>`;
			}).join('')}
		</ul>
	`;
}

function AnalyticsError(message) {
   this.message = message ? message : 'Произошла ошибка! Попробуйте позже.';
   this.name = "AnalyticsError";
}