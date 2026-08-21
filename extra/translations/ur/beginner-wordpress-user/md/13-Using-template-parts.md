# Urdu Translation: Using template parts

| Field | Value |
| --- | --- |
| Lesson | 13 of 24 |
| Module | Site Editing |
| Course | Beginner WordPress User |
| Locale | Urdu (ur) |
| Course issue | #3613 |
| Lesson sub-issue | not yet created |
| Lesson URL | https://learn.wordpress.org/lesson/using-template-parts/ |
| Translator | Azhar Ali (softglaze) |
| Reviewer | Not yet assigned |
| Status | Translated (draft), awaiting review |

## Glossary

Agreed once and applied identically across all 24 lessons.

| English term | Urdu | Note |
| --- | --- | --- |
| WordPress | WordPress | برانڈ نام، انگریزی میں ہی رکھا گیا ہے |
| website | ویب سائٹ |  |
| blog | بلاگ |  |
| post | پوسٹ | "تحریر" کے بجائے پوسٹ، کیونکہ یہ WordPress کا مخصوص تصور ہے |
| page | صفحہ |  |
| theme | تھیم |  |
| plugin | پلگ اِن |  |
| block | بلاک |  |
| block editor | بلاک ایڈیٹر |  |
| Site Editor | سائٹ ایڈیٹر |  |
| dashboard | ڈیش بورڈ |  |
| template | ٹیمپلیٹ |  |
| pattern | پیٹرن |  |
| media | میڈیا |  |
| content | مواد |  |
| settings | ترتیبات |  |
| open source | اوپن سورس |  |
| code | کوڈ |  |
| download | ڈاؤن لوڈ |  |
| install | انسٹال |  |
| publish | شائع کرنا | اسم کے طور پر: اشاعت |
| draft | مسودہ |  |
| security | سیکیورٹی |  |
| backup | بیک اپ |  |
| SEO | ایس ای او | انگریزی مخفف SEO؛ پہلی وضاحت: سرچ انجن آپٹیمائزیشن |
| domain name | ڈومین نام |  |
| host / hosting | ہوسٹ / ہوسٹنگ |  |

## Lesson Title Translation

1 rows.

| # | English | Urdu (اردو) |
| ---: | --- | --- |
| 0 | Using template parts | ٹیمپلیٹ پارٹس کا استعمال |

## Lesson Transcript Translation

18 rows.

| # | English | Urdu (اردو) |
| ---: | --- | --- |
| 1 | Replacing a header or footer: Select the template part in the List View and select one of the design patterns in the right sidebar. | ہیڈر یا فوٹر بدلنا: List View میں ٹیمپلیٹ پارٹ منتخب کیجیے اور دائیں سائیڈبار میں موجود ڈیزائن پیٹرن میں سے کوئی ایک چن لیجیے۔ |
| 2 | _[heading]_<br>Introduction | _[heading]_<br>تعارف |
| 3 | In this lesson, we will look closer at using template parts. By the end of this tutorial, you’ll be able to describe what template parts are and how they work, edit a template part, and replace it on a template. If you visit a well-designed website, you’ll notice that one of its strengths is its consistency. Its headers, footers, and sidebars often have the same or similar content to make it easy for viewers to find the information they are looking for, no matter where they are on a website. | اس سبق میں ہم ٹیمپلیٹ پارٹس کے استعمال کو قریب سے دیکھیں گے۔ اس ٹیوٹوریل کے اختتام تک آپ بتا سکیں گے کہ ٹیمپلیٹ پارٹس کیا ہوتے ہیں اور کیسے کام کرتے ہیں، ٹیمپلیٹ پارٹ میں ترمیم کر سکیں گے، اور کسی ٹیمپلیٹ میں اسے بدل سکیں گے۔ کسی اچھی ڈیزائن کی گئی ویب سائٹ پر جائیں تو آپ دیکھیں گے کہ اس کی ایک بڑی خوبی اس کی یکسانیت ہے۔ اس کے ہیڈر، فوٹر اور سائیڈبار میں اکثر ایک جیسا یا ملتا جلتا مواد ہوتا ہے تاکہ دیکھنے والوں کو مطلوبہ معلومات آسانی سے مل جائیں، چاہے وہ ویب سائٹ کے کسی بھی حصے میں ہوں۔ |
| 4 | To get this effect, do web designers have to build their headers, footers, and sidebars from scratch on every website page? The short answer is no. With WordPress block themes, you will use a feature known as template parts. | یہ اثر پیدا کرنے کے لیے کیا ویب ڈیزائنرز کو ویب سائٹ کے ہر صفحے پر اپنے ہیڈر، فوٹر اور سائیڈبار نئے سرے سے بنانے پڑتے ہیں؟ مختصر جواب ہے: نہیں۔ WordPress کے بلاک تھیمز میں آپ ایک سہولت استعمال کریں گے جسے ٹیمپلیٹ پارٹس کہتے ہیں۔ |
| 5 | What are template parts? | ٹیمپلیٹ پارٹس کیا ہیں؟ |
| 6 | Firstly, what are template parts? Template parts are groups of blocks you can use to create repeated parts of your template, like the header, footer, and sidebar. Let’s see this in action when you’re on your WordPress dashboard. Make your way to Appearance and then click on Editor. This will take us to the Site Editor, and then we’ll open up one of our templates, in this case, the blog home template. | سب سے پہلے، ٹیمپلیٹ پارٹس ہیں کیا؟ ٹیمپلیٹ پارٹس بلاکس کے ایسے گروہ ہوتے ہیں جن سے آپ اپنے ٹیمپلیٹ کے بار بار آنے والے حصے بناتے ہیں، جیسے ہیڈر، فوٹر اور سائیڈبار۔ آئیے اسے اپنے WordPress ڈیش بورڈ میں عملی طور پر دیکھتے ہیں۔ Appearance میں جائیے اور پھر Editor پر کلک کیجیے۔ یہ ہمیں سائٹ ایڈیٹر میں لے جائے گا، اور پھر ہم اپنا کوئی ٹیمپلیٹ کھولیں گے، اس مثال میں Blog Home ٹیمپلیٹ۔ |
| 7 | We create and modify our header and footer template parts within a template. Some block themes provide more or fewer options, but almost all come pre-packaged with the header and footer as you see here as part of the Twenty Twenty-Four theme. WordPress has made it even simpler by providing header and footer patterns that are ready to be used and modified. So when you click on the three vertical dots of the header template part and select Replace header, you will first notice the existing template parts. These are template parts that I’ve already created. Then, below that, we will find header template part patterns. You can select any of these patterns that come with your theme. As mentioned, some themes might provide more, some less. This works exactly the same for footers. | ہم اپنے ہیڈر اور فوٹر ٹیمپلیٹ پارٹس ٹیمپلیٹ کے اندر ہی بناتے اور بدلتے ہیں۔ کچھ بلاک تھیمز زیادہ اختیارات دیتے ہیں اور کچھ کم، لیکن تقریباً سب کے ساتھ ہیڈر اور فوٹر پہلے سے موجود ہوتے ہیں، جیسا کہ آپ یہاں Twenty Twenty-Four تھیم میں دیکھ رہے ہیں۔ WordPress نے اسے مزید آسان بنا دیا ہے اور ایسے ہیڈر اور فوٹر پیٹرن دے دیے ہیں جو استعمال اور تبدیلی کے لیے تیار ہیں۔ چنانچہ جب آپ ہیڈر ٹیمپلیٹ پارٹ کے تین عمودی نقطوں پر کلک کر کے Replace header منتخب کرتے ہیں تو آپ کو پہلے موجودہ ٹیمپلیٹ پارٹس نظر آئیں گے۔ یہ وہ ٹیمپلیٹ پارٹس ہیں جو میں پہلے ہی بنا چکا ہوں۔ اس کے نیچے ہمیں ہیڈر ٹیمپلیٹ پارٹ کے پیٹرن ملیں گے۔ آپ اپنے تھیم کے ساتھ آنے والے ان پیٹرن میں سے کوئی بھی منتخب کر سکتے ہیں۔ جیسا کہ بتایا گیا، کچھ تھیمز زیادہ دیتے ہیں اور کچھ کم۔ فوٹر کے لیے بھی بالکل یہی طریقہ ہے۔ |
| 8 | _[heading]_<br>Editing a template part | _[heading]_<br>ٹیمپلیٹ پارٹ میں ترمیم |
| 9 | Let’s start by editing a header template part. When you click on Edit in the blog toolbar, you can work on your header template part in editing mode without any other distractions. Firstly, let’s open our List View. Then, I’ll go ahead and add my site logo to my header template part. Once I select the Site Logo block, I can upload an image from my media library or computer. | آئیے ہیڈر ٹیمپلیٹ پارٹ میں ترمیم سے شروع کرتے ہیں۔ بلاک ٹول بار میں Edit پر کلک کریں تو آپ اپنے ہیڈر ٹیمپلیٹ پارٹ پر ترمیم کی حالت میں بغیر کسی اور خلل کے کام کر سکتے ہیں۔ پہلے اپنا List View کھولتے ہیں۔ پھر میں اپنے ہیڈر ٹیمپلیٹ پارٹ میں سائٹ کا لوگو شامل کروں گا۔ Site Logo بلاک منتخب کرنے کے بعد میں اپنی میڈیا لائبریری یا کمپیوٹر سے کوئی تصویر اپ لوڈ کر سکتا ہوں۔ |
| 10 | Next, I’ll select the Site Title block and add the site title for this website. Then, I will save my header template part and return to my blog home template. Just a reminder: Template parts are synced and will be updated everywhere they have been used. You will notice that the color purple indicates when a pattern is synced. Let’s see this in practice in one of our other templates. So when we open our page template, for example, we’ll see the header is exactly the same. Or when we open up our 404 template, we’ll see that the changes have also applied here because template parts are synced. | اس کے بعد میں Site Title بلاک منتخب کروں گا اور اس ویب سائٹ کے لیے سائٹ کا عنوان شامل کروں گا۔ پھر میں اپنا ہیڈر ٹیمپلیٹ پارٹ محفوظ کر کے واپس اپنے Blog Home ٹیمپلیٹ پر آ جاؤں گا۔ ایک یاد دہانی: ٹیمپلیٹ پارٹس ہم آہنگ ہوتے ہیں اور جہاں جہاں استعمال ہوئے ہوں وہاں سب جگہ اپ ڈیٹ ہو جائیں گے۔ آپ دیکھیں گے کہ جامنی رنگ بتاتا ہے کہ کوئی پیٹرن ہم آہنگ ہے۔ آئیے اسے اپنے کسی دوسرے ٹیمپلیٹ میں عملی طور پر دیکھتے ہیں۔ مثال کے طور پر جب ہم اپنا Page ٹیمپلیٹ کھولیں گے تو ہمیں ہیڈر بالکل ویسا ہی نظر آئے گا۔ یا جب ہم اپنا 404 ٹیمپلیٹ کھولیں گے تو دیکھیں گے کہ تبدیلیاں یہاں بھی لاگو ہو گئی ہیں، کیونکہ ٹیمپلیٹ پارٹس ہم آہنگ ہوتے ہیں۔ |
| 11 | What if you wanted a different header on your home page, for example, but you would like the rest of your website to have a standard header? In this case, you would want to create a new header template part or replace the header with a pattern and add it to your blog home or front page template. I will modify my blog home template as I’ve selected my posts page as my home page display. So I’ll make sure I select my header, click on the three vertical dots, and then select Replace header. Now, you can replace your home page header using a pattern or an existing template part. I will replace the header with an existing header template part, which I’ve already created and called Banner Header. Now, my home page will have its own unique header. Once I’ve saved it, we can view our other templates to double-check that they still have the standard header in place. | لیکن اگر آپ چاہیں کہ مثال کے طور پر آپ کے ہوم پیج پر کوئی مختلف ہیڈر ہو جبکہ باقی پوری ویب سائٹ پر معمول کا ہیڈر رہے تو؟ ایسی صورت میں آپ کو نیا ہیڈر ٹیمپلیٹ پارٹ بنانا ہوگا، یا ہیڈر کی جگہ کوئی پیٹرن رکھ کر اسے اپنے Blog Home یا Front Page ٹیمپلیٹ میں شامل کرنا ہوگا۔ چونکہ میں نے ہوم پیج کی نمائش کے لیے اپنا پوسٹس صفحہ منتخب کیا ہے، اس لیے میں اپنا Blog Home ٹیمپلیٹ بدلوں گا۔ تو میں اپنا ہیڈر منتخب کروں گا، تین عمودی نقطوں پر کلک کروں گا، اور پھر Replace header منتخب کروں گا۔ اب آپ اپنے ہوم پیج کا ہیڈر کسی پیٹرن یا کسی موجودہ ٹیمپلیٹ پارٹ سے بدل سکتے ہیں۔ میں ہیڈر کی جگہ اپنا وہ موجودہ ہیڈر ٹیمپلیٹ پارٹ رکھوں گا جو میں پہلے ہی بنا چکا ہوں اور جس کا نام Banner Header ہے۔ اب میرے ہوم پیج کا اپنا منفرد ہیڈر ہوگا۔ محفوظ کرنے کے بعد ہم اپنے دوسرے ٹیمپلیٹس دیکھ کر تصدیق کر سکتے ہیں کہ ان پر معمول کا ہیڈر بدستور موجود ہے۔ |
| 12 | _[heading]_<br>Creating a template part from scratch | _[heading]_<br>نئے سرے سے ٹیمپلیٹ پارٹ بنانا |
| 13 | Lastly, let’s talk about creating a template part from scratch. Make your way to Patterns and then scroll down to template parts. You can view all your existing headers, footers, and general template parts here. To create a new template part, click the plus icon next to patterns and select Create template part. From here, you can select a general template part that is not tied to any particular area, a header template part, or a footer template part. In this case, we are going to create a footer template part. | آخر میں، نئے سرے سے ٹیمپلیٹ پارٹ بنانے کی بات کرتے ہیں۔ Patterns میں جائیے اور پھر نیچے Template Parts تک اسکرول کیجیے۔ یہاں آپ اپنے تمام موجودہ ہیڈر، فوٹر اور عمومی ٹیمپلیٹ پارٹس دیکھ سکتے ہیں۔ نیا ٹیمپلیٹ پارٹ بنانے کے لیے Patterns کے ساتھ جمع کے نشان پر کلک کیجیے اور Create template part منتخب کیجیے۔ یہاں سے آپ عمومی ٹیمپلیٹ پارٹ چن سکتے ہیں جو کسی مخصوص جگہ سے بندھا نہ ہو، یا ہیڈر ٹیمپلیٹ پارٹ، یا فوٹر ٹیمپلیٹ پارٹ۔ اس مثال میں ہم فوٹر ٹیمپلیٹ پارٹ بنانے جا رہے ہیں۔ |
| 14 | Firstly, we need to name it appropriately. In this case, a Four-column footer, and then we can click Create. This will take us into focus mode again; from here, we can start building from scratch. I will take a shortcut and grab a pattern from the Patterns Directory. Make your way to wordpress.org, click on Extend, and select Patterns. From here, you can select from thousands of patterns. I will select Community Contributions, click on Footers, and then you can search for the right pattern. Once you find a pattern that appeals to you, you can copy the pattern, return to your website and paste. Now you can start modifying it to meet your needs. Once updated, we can return to the patterns area, and you will notice that your new footer is part of your existing template parts. You can add it to any template. When you select Manage all template parts, you can rename and delete custom template parts or clear the customizations of template parts provided by your theme. I trust you now feel comfortable editing your templates and creating a header and footer for your site. | سب سے پہلے ہمیں اسے مناسب نام دینا ہوگا۔ اس مثال میں چار کالموں والا فوٹر، اور پھر ہم Create پر کلک کر سکتے ہیں۔ یہ ہمیں دوبارہ توجہ والی حالت میں لے جائے گا؛ یہاں سے ہم نئے سرے سے بنانا شروع کر سکتے ہیں۔ میں ایک شارٹ کٹ اختیار کروں گا اور پیٹرن ڈائریکٹری سے کوئی پیٹرن اٹھا لوں گا۔ wordpress.org پر جائیے، Extend پر کلک کیجیے، اور Patterns منتخب کیجیے۔ یہاں سے آپ ہزاروں پیٹرن میں سے چن سکتے ہیں۔ میں Community Contributions منتخب کروں گا، Footers پر کلک کروں گا، اور پھر آپ اپنا مطلوبہ پیٹرن تلاش کر سکتے ہیں۔ جو پیٹرن آپ کو پسند آ جائے، اسے نقل کیجیے، اپنی ویب سائٹ پر واپس آ کر چسپاں کر دیجیے۔ اب آپ اسے اپنی ضرورت کے مطابق بدلنا شروع کر سکتے ہیں۔ اپ ڈیٹ ہو جانے کے بعد ہم واپس پیٹرن والے حصے میں آ سکتے ہیں، اور آپ دیکھیں گے کہ آپ کا نیا فوٹر آپ کے موجودہ ٹیمپلیٹ پارٹس کا حصہ بن چکا ہے۔ آپ اسے کسی بھی ٹیمپلیٹ میں شامل کر سکتے ہیں۔ Manage all template parts منتخب کریں تو آپ اپنے مخصوص ٹیمپلیٹ پارٹس کے نام بدل اور انہیں حذف کر سکتے ہیں، یا اپنے تھیم کے دیے ہوئے ٹیمپلیٹ پارٹس میں کی گئی تبدیلیاں ہٹا سکتے ہیں۔ مجھے یقین ہے کہ اب آپ اپنے ٹیمپلیٹس میں ترمیم کرنے اور اپنی سائٹ کے لیے ہیڈر اور فوٹر بنانے میں آسانی محسوس کریں گے۔ |
| 15 | _[heading]_<br>Practical | _[heading]_<br>عملی مشق |
| 16 | Go to WordPress Playground and complete the following activities to test your knowledge: | اپنی معلومات آزمانے کے لیے WordPress Playground پر جائیے اور یہ سرگرمیاں مکمل کیجیے: |
| 17 | Replace the current header of the Blog Home template with any header template part pattern that comes with the theme. | Blog Home ٹیمپلیٹ کے موجودہ ہیڈر کی جگہ تھیم کے ساتھ آنے والا کوئی بھی ہیڈر ٹیمپلیٹ پارٹ پیٹرن رکھیے۔ |
| 18 | Replace the current footer of the Blog Home template with a new footer template pattern that comes with the theme. | Blog Home ٹیمپلیٹ کے موجودہ فوٹر کی جگہ تھیم کے ساتھ آنے والا کوئی نیا فوٹر ٹیمپلیٹ پیٹرن رکھیے۔ |

## Lesson Quiz Translation

This lesson has no quiz.
