[Justuno](https://www.justuno.com) module for Magento 1.

## How to install or upgrade
Execute in the Magento root directory:
```
rm -f app/etc/modules/Justuno_Jumagext.xml ;
rm -rf app/code/community/Justuno ;
rm -rf app/design/adminhtml/default/default/template/justuno ;
rm -rf app/design/frontend/base/default/layout/justuno ;
rm -rf app/design/frontend/base/default/template/justuno  ;
rm -rf apps/justuno ;
ORG=justuno-com ;
REPO=m1 ;
FILE=$REPO.tar.gz ;
VERSION=$(curl -s https://api.github.com/repos/$ORG/$REPO/releases | grep tag_name | head -n 1 | cut -d '"' -f 4) ;
curl -L -o $FILE https://github.com/$ORG/$REPO/archive/$VERSION.tar.gz ;
tar xzvf $FILE ;
rm -f $FILE ;
cp -r $REPO-$VERSION/* . ;
rm -rf $REPO-$VERSION ;
rm -rf var/cache
```

<h2 id="account-number">Where to find my «Justuno Account Number»?</h2>

![](https://mage2.pro/uploads/default/original/2X/4/429d007f47381d01e5eb2d33d762d77fd2e04932.png)  
![](https://mage2.pro/uploads/default/original/2X/3/3ef7cd3ad314c5e2e105f56154385bbe9be0f617.png)
