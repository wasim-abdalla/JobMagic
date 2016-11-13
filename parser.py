#!/usr/bin/env python
from re import findall,sub
from lxml import html
from requests import get
from json import dumps
from sys import argv
from datetime import datetime


def parse(keyword,headers):
    keyword = findall("search indeed (.*)",keyword.lower())
    keyword = keyword[0].strip().title() if keyword else None
    # search_url = 'http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias=aps&field-keywords=%s'%keyword.replace(" ","+")
    search_url = 'http://api.indeed.com/ads/apisearch?publisher=5940159532938846&l=austin%2C+tx&sort=&radius=&st=&jt=&start=&limit=&fromage=&filter=&latlong=1&co=us&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2&q=%s'%keyword.replace(" ","+")
    response = get(search_url,headers=headers)
    parser = html.fromstring(response.content,response.url)
    finalData = []
    results = parser.xpath('//result')
    for i in results[:1]:
        imageUrl = i.xpath('.//img/@src')
        imageUrl = imageUrl[0] if imageUrl else None
        imageUrl = "http://placehold.it/250/250"
        url = i.xpath('.//url/@href')
        url = url[0] if url else None
        prodName = i.xpath('.//jobtitle/text()')
        prodName = prodName[0].strip() if prodName else None
        item = {
                    "imageUrl":imageUrl,
                    "url":url,
                    "prodName":prodName
        }
        finalData.append(item)
    return finalData,keyword

if __name__ == '__main__':
    headers = {
        'Host': 'www.Indeed.com',
        'Connection': 'keep-alive',
        'Pragma': 'no-cache',
        'Cache-Control': 'no-cache',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Upgrade-Insecure-Requests': '1',
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36',
        'DNT': '1',
        'Referer': 'https://www.Indeed.com/',
        'Accept-Encoding': 'gzip, deflate, sdch',
        'Avail-Dictionary': 'Dn5_GnWS',
        'Accept-Language': 'en-US,en;q=0.8'
    }
    data, keyword = parse(argv[1],headers)
    print dumps(data)
    file = open("Search Log.txt","a")
    scrapeDate = datetime.today().strftime("%Y-%m-%dT%H:%M:%S")
    file.write("Searched keyword %s at time %s \n"%(keyword,scrapeDate))
    file.close()
