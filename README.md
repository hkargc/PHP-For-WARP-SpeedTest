# PHP-For-WARP-SpeedTest<br />
优选Cloudflare WARP的Endpoint[PHP实现]<br />

网上有个 warp.exe 实现了优选功能,但没有开源,exe用起来也不方便,于是试试用世界上至少第二好的语言来搞一下^_^<br />

Cloudflare WARP 采用的是UDP协议,有资料说跟wireguard兼容,实际用Wireshark分析看起来并不一致<br />
这里取个巧,把抓的第一个包用于模拟"握手"请求,分析收发包的时间间隔<br />

主要参考了以下项目:<br />
https://github.com/XIU2/CloudflareSpeedTest<br />
https://github.com/Ptechgithub/warp<br />
https://github.com/ddgth/cf2dns<br />

相关资料:<br />
https://developers.cloudflare.com/cloudflare-one/connections/connect-devices/warp/deployment/firewall/<br />
https://www.wireguard.com/protocol/<br />

需要Linux环境:<br />
PHP 7.3 + swoole扩展<br />

Linux:<br />
命令行执行,正常的话应该能看到输出:<br />
[xman@localhost PHP-For-WARP-SpeedTest]$ /path/to/php /path/to/PHP-For-WARP-SpeedTest/warp.php <br />
162.159.193.5:500	0	33.6434056<br />
162.159.193.2:500	0	34.8429088<br />
162.159.193.6:500	0	36.5509916<br />
162.159.193.4:4500	0	36.8462799<br />
162.159.193.7:1701	0	39.5873574<br />
162.159.193.9:500	0	198.6909696<br />

Windows:<br />
C:\Users\xman>warp-cli tunnel endpoint reset<br />
Success<br />
C:\Users\xman>warp-cli tunnel endpoint set 162.159.193.5:500<br />
Success<br />

以上是在本地Linux虚拟机执行,然后在windows上更新endpoint的<br />
