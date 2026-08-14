const https = require('https');

function checkSite(url) {
    https.get(url, (res) => {
        let data = '';
        res.on('data', (chunk) => { data += chunk; });
        res.on('end', () => {
            const urlMap = {};
            const regex1 = /<img[^>]+src=['"]([^'"]+)['"]/gi;
            const regex2 = /url\(['"]?([^'"\)]+)['"]?\)/gi; // For CSS backgrounds
            
            let match;
            while ((match = regex1.exec(data)) !== null) { urlMap[match[1]] = true; }
            while ((match = regex2.exec(data)) !== null) { urlMap[match[1]] = true; }

            console.log(`\n--- Images for ${url} ---`);
            Object.keys(urlMap).forEach(img => {
                if (!img.toLowerCase().includes('logo')) {
                    console.log(img);
                }
            });
        });
    }).on("error", (err) => {
        console.log("Error: " + err.message);
    });
}

checkSite('https://raymondrealtymahim.com/');
checkSite('https://raymondseafacemahim.com/');
checkSite('https://raymondseaview.com/');
