# PHP-For-WARP-SpeedTest
优选Cloudflare WARP的Endpoint[PHP实现]

Cloudflare WARP 采用的是UDP协议,有资料说跟wireguard兼容,实际用Wireshark分析看起来并不一致
这里取个巧,把抓的第一个包用于模拟"握手"请求,分析收发包的时间间隔

主要参考了以下项目:
https://github.com/XIU2/CloudflareSpeedTest
https://github.com/Ptechgithub/warp
https://github.com/ddgth/cf2dns

相关资料:
https://developers.cloudflare.com/cloudflare-one/connections/connect-devices/warp/deployment/firewall/
https://www.wireguard.com/protocol/

需要Linux环境:
PHP 7.3 + swoole扩展

Linux:
命令行执行,正常的话应该能看到输出:
[xman@localhost PHP-For-WARP-SpeedTest]$ /path/to/php /path/to/PHP-For-WARP-SpeedTest/warp.php 
162.159.193.5:500	0	33.6434056
162.159.193.2:500	0	34.8429088
162.159.193.6:500	0	36.5509916
162.159.193.4:4500	0	36.8462799
162.159.193.7:1701	0	39.5873574
162.159.193.9:500	0	198.6909696

Windows:
C:\Users\xman>warp-cli tunnel endpoint reset
Success
C:\Users\xman>warp-cli tunnel endpoint set 162.159.193.5:500
Success

以上是在本地Linux虚拟机执行,然后在windows上更新endpoint的
