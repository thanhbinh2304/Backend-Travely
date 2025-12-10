const https = require('https');
const http = require('http');
const fs = require('fs');

const options = {
    key: fs.readFileSync('./ssl/localhost+2-key.pem'),
    cert: fs.readFileSync('./ssl/localhost+2.pem')
};

https.createServer(options, (req, res) => {
    const proxy = http.request({
        hostname: '127.0.0.1',
        port: 8000,
        path: req.url,
        method: req.method,
        headers: req.headers
    }, (proxyRes) => {
        res.writeHead(proxyRes.statusCode, proxyRes.headers);
        proxyRes.pipe(res);
    });

    req.pipe(proxy);

    proxy.on('error', (err) => {
        console.error('Proxy error:', err);
        res.writeHead(500);
        res.end('Proxy error');
    });
}).listen(8443, () => {
    console.log('HTTPS proxy running on https://127.0.0.1:8443');
    console.log('Proxying to http://127.0.0.1:8000');
});
