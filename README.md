You must have docker-compose installed on your computer

Edit your hosts file:
```
vim /etc/hosts 
```
Insert new line ```simpleblog.local 127.0.0.1```

Then execute ```docker-compose up -d``` in the terminal and load http://simpleblog.local:8080/ in your browser. 