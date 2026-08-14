const https = require('https');

https.get('https://raymondseaview.com/', (res) => {
    let data = '';
    res.on('data', (chunk) => {
        data += chunk;
    });

    res.on('end', () => {
        const urlMap = {};
        const regex1 = /<img[^>]+src=['"]([^'"]+)['"]/g;
        const regex2 = /data-src=['"]([^'"]+)['"]/g;
        
        let match;
        while ((match = regex1.exec(data)) !== null) {
            urlMap[match[1]] = true;
        }
        while ((match = regex2.exec(data)) !== null) {
            urlMap[match[1]] = true;
        }

        const uniqueImgs = Object.keys(urlMap).map(img => {
            if (img.startsWith('http')) return img;
            if (img.startsWith('//')) return 'https:' + img;
            if (img.startsWith('/')) return 'https://raymondseaview.com' + img;
            return 'https://raymondseaview.com/' + img;
        });

        uniqueImgs.forEach(img => {
            if (!img.toLowerCase().includes('logo')) {
                console.log(img);
            }
        });
    });
}).on("error", (err) => {
    console.log("Error: " + err.message);
});
