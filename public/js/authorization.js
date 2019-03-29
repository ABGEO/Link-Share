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