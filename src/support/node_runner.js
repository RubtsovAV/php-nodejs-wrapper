(function (program, runner) {
    runner(program)
})(function () {
    //--SOURCE--//
}, function (program) {
    var output, print = function (string) {
        process.stdout.write('' + string);
    };
    try {
        result = program();
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
