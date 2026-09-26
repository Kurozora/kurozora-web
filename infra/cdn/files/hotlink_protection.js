// CloudFront Function — viewer-request
// Blocks external Referers on cdn.kurozora.app while letting through:
//   * the Kurozora mobile app (UA starts with "Kurozora/")
//   * requests with no Referer (direct visits, native clients, web crawlers)
//   * Referers from kurozora.app, any *.kurozora.app subdomain, or local kurozora.test
function handler(event) {
    let request = event.request
    let headers = request.headers

    let ua = headers['user-agent'] && headers['user-agent'].value;
    if (ua && ua.indexOf('Kurozora/') === 0) {
        return request;
    }

    let referer = headers['referer'] && headers['referer'].value;
    if (!referer) {
        return request;
    }

    let match = referer.match(/^https?:\/\/([^\/?:#]+)/);
    let host = match ? match[1].toLowerCase() : '';
    if (host === 'kurozora.app' || host === 'kurozora.test' || host.length > 13 && host.substring(host.length - 13) === '.kurozora.app') {
        return request;
    }

    return {
        statusCode: 403,
        statusDescription: 'Forbidden',
        headers: {
            'content-type': { value: 'text/plain' },
            'cache-control': { value: 'no-store' }
        },
        body: 'Hotlinking not allowed.'
    };
}
