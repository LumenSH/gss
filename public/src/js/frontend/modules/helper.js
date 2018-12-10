class Helper {
    static request(url, data, headers) {
        let method = 'GET';
        data = data || {};
        headers = headers || {};
        let body = '';

        if (Object.keys(data)) {
            Object.keys(data).forEach((key) => {
                body += key + '=' + encodeURIComponent(data[key]) + '&';
            });
            body = body.substr(0, body.length - 1);
            method = 'POST';
            headers = {
                "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
            };
        }

        return fetch(url, {
            method: method,
            credentials: 'include',
            headers: headers,
            body: body
        })
    }
}

export {Helper}