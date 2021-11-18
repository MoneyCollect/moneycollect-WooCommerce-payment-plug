Moneycollect WooCommerce Payment Plug
=======

1.下载zip插件包
----
把文件重命名为：moneycollect-payments-gateway.zip

2.在wordpress商城安装插件
----
选择下载好的插件安装
![install](https://user-images.githubusercontent.com/92731686/142347345-c44aacd1-fd4e-4e61-869d-8b16c1c50a28.png)

3.进入设置
----

WooCommerce -> Settings -> Payments -> MoneyCollect Credit Card

![setting](https://user-images.githubusercontent.com/92731686/142347728-abf372ad-69a9-4612-afc9-d63e19472561.png)


![setting1](https://user-images.githubusercontent.com/92731686/142347903-c7ad7064-8a78-475b-891a-c21981b9c8be.png)


4.托管模式交易
---
![checkout3](https://user-images.githubusercontent.com/92731686/142348504-a3dc1a29-b80d-49b2-a041-ca3653fd68b1.png)


![checkout](https://user-images.githubusercontent.com/92731686/142348181-cf5f27a8-739d-4a9e-ada8-a0043cbacd22.png)


![checkout2](https://user-images.githubusercontent.com/92731686/142348270-1260b319-67ad-49ec-b8b5-a4f407c0271c.png)


4.内嵌模式交易
---
![checkout4](https://user-images.githubusercontent.com/92731686/142348592-b9956722-9f48-4ed4-a453-c61153adde23.png)

![checkout5](https://user-images.githubusercontent.com/92731686/142348708-da410804-62e0-4f2a-897c-6987060a232a.png)


5.Additional testing resources
---
There are several test cards you can use to make sure your integration is ready for production. Use them with any CVC, postal code, and future expiration date.

|  Card Number| Brand  |DESCRIPTION          |
| :------------- | :------------- | :-------------- |
| 4242 4242 4242 4242    | Visa            | Succeeds and immediately processes the payment. |
| 3566 0020 2036 0505    | JCBA            | Succeeds and immediately processes the payment. |
| 6011 1111 1111 1117    | Discover        | Succeeds and immediately processes the payment. |
| 3782 8224 6310 0052    | American Express| Succeeds and immediately processes the payment. |
| 5555 5555 5555 4444    | Mastercard      | Succeeds and immediately processes the payment. |
| 4000 0025 0000 3155    | Visa            | 3D Secure 2 authentication . |
| 4000 0000 0000 0077    | Visa            | Always fails with a decline code of `declined`. |
