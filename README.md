# php-nodejs-wrapper

Php library allows you to execute JavaScript code using the node js.

##Example
```
use Kurbits\JavaScript\NodeRunner;

$nodejs = new NodeRunner();

// you can set js source code by one string
$nodejs->setSource('y = function(x){return x*x;}');

// and call js functions like this
echo $nodejs->call('y', 3);     // print 9

// or like this
echo $nodejs->execute('y(3)');  // print 9

// Also you can set many sources as array
// old sources will be deleted
$nodejs->setSources([
    'y = function(x) { return x*x; }',
    'z = function(x, y) { return x + y; }',
]);
echo $nodejs->execute('z(3, y(3))');  // print 12

// Example with the grab of output
$nodejs->addSource('document = {
    write: function(string) {
        process.stdout.write(string); 
    }
};');
$nodejs->addSource('document.write(z(3, y(3)))');
echo $nodejs->execute();  // print 12
```
