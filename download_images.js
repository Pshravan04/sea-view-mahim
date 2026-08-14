const https = require('https');
const fs = require('fs');
const path = require('path');

const imgsToDownload = [
    'https://raymondseaview.com/img/aboutproject.webp',
    'https://raymondseaview.com/img/floorplans/floorplan1.webp',
    'https://raymondseaview.com/img/floorplans/floorplan2.webp',
    'https://raymondseaview.com/img/gallery/gal01.webp',
    'https://raymondseaview.com/img/gallery/gal02.webp',
    'https://raymondseaview.com/img/gallery/gal03.webp',
    'https://raymondseaview.com/img/gallery/gal04.webp',
    'https://raymondseaview.com/img/gallery/gal05.webp',
    'https://raymondseaview.com/img/gallery/gal06.webp',
    'https://raymondseaview.com/img/gallery/gal07.webp',
    'https://raymondseaview.com/img/locmap2.webp'
];

const destDir = path.join(__dirname, 'assets', 'extracted');

imgsToDownload.forEach(url => {
    const filename = path.basename(url);
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
