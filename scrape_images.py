import urllib.request
import re
from urllib.parse import urljoin

url = 'https://raymondseaview.com/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    html = urllib.request.urlopen(req).read().decode('utf-8')
    img_tags = re.findall(r'<img[^>]+src=[\'"]([^\'">]+)[\'"][^>]*>', html)
    img_tags += re.findall(r'data-src=[\'"]([^\'">]+)[\'"]', html)
    
    unique_imgs = set()
    for img in img_tags:
        full_url = urljoin(url, img)
        if 'logo' not in full_url.lower():
            unique_imgs.add(full_url)
    
    for img in unique_imgs:
        print(img)
except Exception as e:
    print('Error:', e)
