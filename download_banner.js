const https = require('https');
const fs = require('fs');
const path = require('path');

const imgsToDownload = [
    'https://raymondrealtymahim.com/wp-content/uploads/2023/12/Raymond-Realty-mahim-web-banner.webp'
];

const destDir = path.join(__dirname, 'assets', 'extracted');

imgsToDownload.forEach(url => {
    const filename = 'Raymond-Realty-mahim-web-banner.webp';
    const dest = path.join(destDir, filename);
    const file = fs.createWriteStream(dest);
    
    https.get(url, (res) => {
        if (res.statusCode !== 200) {
            console.error(`Failed to get ${url} - Status Code: ${res.statusCode}`);
            return;
        }
        res.pipe(file);
        file.on('finish', () => {
            file.close();
            console.log(`Downloaded: ${filename}`);
        });
    }).on('error', (err) => {
        console.error(`Error downloading ${url}: ${err.message}`);
    });
});
