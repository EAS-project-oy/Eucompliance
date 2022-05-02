 <img alt="EAS" width="150px" src="doc/static/Plugin_logo.png" align="left"  HSPACE=”50” VSPACE=”50”/>
 EAS EU compliance plugin gives easy and unimpeded access to the whole EU market, your sales are always VAT compliant irrespective of where you sell from and your registry/legal environment.
All complicated tax schemes and situations are covered automatically – you involve yourself in making sales , not manual accounting reports
<br/>
<br/>
EAS integration module is available for both newest Magento versions 2.4.* and  2.3.5 version.

### You may install this module using composer or git

*Note

To install this module, your composer version must be 2 or higher.

In case your installation is not compatible with the latest composer version, you should use git to install it.

### **To install this module using composer**

Use composer require eas/eucompliance:dev-master (for latest version for magento 2.4.*) php 7.4

Or

`composer require eas/eucompliance:dev-feature/2.3.5  ( In case your magento version is magento 2.3.5)
`

You should get following result
<p align="center">
    <img alt="EAS composer" src="doc/static/composer_install.png" />
</p>

### **To install this module using git**

Our application requires "firebase/php-jwt" version 5.3 and higher
Please install "firebase/php-jwt" before using 'composer require firebase/php-jwt 5.3


Firstly,  create Eas package in app/code directory

`mkdir app/code/Eas
`

After that navigate to the newly created directory

`cd app/code/Eas
`

And clone module

`git clone https://github.com//EAS-project-oy/eascompliance  eucompliance
`

After installation from root magento folder,
to enable module run: `bin/magento se:up`
to generate module classes run: `bin/magento s:s:d -f && bin/magento s:d:c`
You can run it all in one command: `bin/magento se:up && bin/magento s:s:d -f && bin/magento s:d:c`

After doing so, `bin/magento mo:st Eas_Eucompliance` should return

<p align="center">
    <img alt="EAS mo st" src="doc/static/mo_st.png" />
</p>

Which means, module is successfully installed and enabled.

After installation, you need to configure the module.

To do so, navigate to Stores → Configuration section in admin. You should be able to see EAS EU Compliance tab

<p align="center">
    <img alt="EAS main_menu" src="doc/static/main_menu.png" />
</p>

Expand it, and click the General tab.

After navigating, you will see 4 sections

_General_

Enable this solution  -  Enable/Disable module

Shipping methods by post - select shipping methods which use post mailing system.

*Note All non selected methods will be considered as delivering using courier.

_**Credentials**_ - get default urls from  https://dashboard.easproject.com/

**EAS API Base URL**  -  base api url used for sending request

**EAS API authorize URL** - url, which will be used during  authorization

**EAS API auth keys URL**  - url, which will be used to get authorized keys

**EAS API calculate URL** -  url, which will be used to calculate additional costs

**EAS api key**  -  EAS credentials, which will be used during authorization (EAS should provide them after signing in contract)

**EAS secret api key** - EAS credentials, which will be used during authorization (EAS should provide them after signing in contract)

_**Attributes**_  - Those are product attributes, which will be used during calculation process.

**HSCODE** - Change if you already use another attribute to specify hscode.

**Reduced vat** -  Change if you already use another attribute to specify reduced vat.

**Warehouse country**  - Change if you already use another attribute for warehouse country.

_Advanced_

**Debug mode** - Enable/Disable debug mode, which will write data in debug log.

Those data contains data sent to api, data get from api, all errors.

#### Configuration EAS attributes on product level. For it, we should go to edit a product page in admin panel.

You can configure eas attributes, during product edition in EAS EU Compliance tab.
<p align="center">
    <img alt="EAS eas_product_edit" src="doc/static/eas_product_edit.png" />
</p>


After the module is configured and enabled, you can test it.

During the checkout process, after shipping data is entered, and the shipping method is chosen, if you click on Next button, you should be redirected to EAS. Otherwise, you will see an error at the top of your screen.

<p align="center">
    <img alt="EAS button_next" src="doc/static/button_next.png" />
</p>
<p align="center">
    <img alt="EAS button_next" src="doc/static/confirm_page.png" />
</p>
### **Eas confirmation page**

If user accepts EAS additional costs, EAS will redirect customer to payment section.  Otherwise, it will redirect customer to cart section.
<p align="center">
    <img alt="EAS decline_confirm" src="doc/static/decline_confirm.png" />
</p>

After Confirm we redirect the Customer to checkout payment with updated data. Where he can choose a payment method and then finish his order.
<p align="center">
    <img alt="EAS cart_data" src="doc/static/cart_data.png" />
</p>

On Decline - we redirect the customer to cart section.
<p align="center">
    <img alt="EAS shopping_cart" src="doc/static/shopping_cart.png" />
</p>


