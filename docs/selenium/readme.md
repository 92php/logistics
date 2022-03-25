使用selenium自动登录店小秘
=======================
## 使用步骤
- 第一步:安装最新的Chrome和chromedriver,并确保它们是互相兼容的。chromedriver的下载地址:http://chromedriver.storage.googleapis.com/index.html,在每一个chrome驱动版本的目录下存在`notes.txt`，其中包含该驱动版本所支持的Chrome版本,请根据系统和Chrome版本下载对应驱动。下载后解压出来的chromedriver.exe应放在php.exe同一目录下.

- 第二步:安装java, 在开启selenium服务前确保有java 8以上的环境。

- 第三步:开启selenium服务:进入http://selenium-release.storage.googleapis.com/index.html下载`selenium-server-standalone-#.jar`文件。下载完成后使用命令`java -jar selenium-server-standalone-#.jar`(将 # 替换为下载的selenium服务版本)就可以开启selenium服务了