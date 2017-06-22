# mwbot-twitter
*Simple Twitter Bot that posts pictures with associated metadata.*

## Usage

Just simply clone this repo, install required dependencies via 
 
```bash
composer install
``` 
 
and run the `setup.php` to set up everything. More information on how to install/use composer can be found [here](https://getcomposer.org)

Next add your metadata files as **.txt** files inside the `media` folder and the corresponding
media inside pics so it looks something like this:

```text
├───media
│   │   1.txt
│   │   2.txt
│   │   3.txt
│   │
│   └───pics
│           1.jpg
│           2.jpg
│           3.jpg
```

**Your metadata files essentially just contain the text to be tweeted with the associated picture.** f.e.:

```text
A Maned Wolf at Beardsley Zoo
```

You also need to adjust the `$afterTweet` message inside the `generator.php`. It defaults to ` | #ManedWolf`

After that you can set up an cron job to let the bot tweet automatically.
You could use something like this:

```cron
*/10 * * * * /usr/bin/php /path/to/generator.php >/dev/null 2>&1
```