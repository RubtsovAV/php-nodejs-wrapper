(function (program, runner) {
    runner(program)
})(function () {
    //--SOURCE--//
}, function (program) {
    var output = '',
        sourceWrite = process.stdout.write,
        print = function (string) {
            sourceWrite.call(process.stdout, '' + string);
        };
    
    process.stdout.write = function (string) { 
        output += string; 
    };
        
    try {        
        result = program();
        if (output.length > 0) {
            if (result) {
                result = output + result;
            } else {
                result = output;
            }
        }
        if (typeof result == 'undefined' && result !== null) {
            print('["ok"]');
        } else {
            try {
                print(JSON.stringify(['ok', result]));
            } catch (err) {
                print(JSON.stringify(['err', '' + err, err.stack]));
            }
        }
    } catch (err) {
        print(JSON.stringify(['err', '' + err]));
    }
});
