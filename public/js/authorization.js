/**
 * Show Sign In, Sign Up, or Forgot forms
 *
 * @param formID Form ID For showing
 */
function showForm(formID) {
    $("#signInBox").prop("hidden", true);
    $("#signUpBox").prop("hidden", true);
    $("#forgotBox").prop("hidden", true);

    $("#" + formID).prop("hidden", false);
}

/**
 * User Sign Up
 */
$('#signUpSubmit').click(function () {
    let form = $('form[name=signUpForm]');
    let token = $('input[name=signUpToken]');
    let email = $('input[name=signUpEmail]');
    let pass = $('input[name=signUpPassword]');
    let repPass = $('input[name=signUpRepPassword]');
    let btn = $('#signUpSubmit');

    let  re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;

    toastr.clear();
    btn.prop("disabled", true);

    if (!re.test(String(email.val()).toLowerCase())) {
        toastr.error('Please, enter valid E-Mail!');
        email.focus();
        btn.prop("disabled", false);
    } else if (pass.val().length < 6) {
        toastr.error('Password must contain at least 6 characters!');
        pass.focus();
        btn.prop("disabled", false);
    } else if (pass.val() !== repPass.val()) {
        toastr.error("Passwords don't match!");
        repPass.focus();
        btn.prop("disabled", false);
    } else {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: {
                token: token.val(),
                email: email.val(),
                password: pass.val()
            },
            cache: false,
            success: function (response) {
                if (response === 'userExists') {
                    toastr.error('E-mail address is busy!');
                } else if (response === 'successReg') {
                    toastr.success('You\'ve successfully registered. Please, Log In into your account.');
                } else {
                    console.log(response);
                    toastr.error('An error has occurred. Try again!');
                }

                btn.prop("disabled", false);
            },
            error: function (response) {
                //Error response
                console.log(response);
                toastr.error('An error has occurred. Try again!');
                btn.prop("disabled", false);
            }
        });
    }

});

/**
 * User Sign In
 */
$('#signInSubmit').click(function () {
    let form = $('form[name=signInForm]');
    let token = $('input[name=signInToken]');
    let email = $('input[name=signInEmail]');
    let pass = $('input[name=signInPassword]');
    let btn = $('#signInSubmit');

    toastr.clear();
    btn.prop("disabled", true);

    if (email.val() === '') {
        toastr.error('Please, enter you\'r E-Mail!');
        email.focus();
        btn.prop("disabled", false);
    } else if (pass.val() === '') {
        toastr.error('Please, enter you\'r password!');
        pass.focus();
        btn.prop("disabled", false);
    } else {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: {
                token: token.val(),
                email: email.val(),
                password: pass.val()
            },
            cache: false,
            success: function (response) {
                if (response === 'invalidCredentials') {
                    toastr.error('Invalid credentials!', {timeOut: 5000});
                } else if (response === 'successLogIn') {
                    toastr.success('Successful authorization!', {timeOut: 2000});
                    setTimeout(function () {
                        location.reload();
                    }, 2000)
                } else {
                    console.log(response);
                    toastr.error('An error has occurred. Try again!');
                }

                btn.prop("disabled", false);
            },
            error: function (response) {
                //Error response
                console.log(response);
                toastr.error('An error has occurred. Try again!');
                btn.prop("disabled", false);
            }
        });
    }
});