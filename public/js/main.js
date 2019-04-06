/**
 * Toastr settings.
 *
 * @type {{newestOnTop: boolean, debug: boolean, showMethod: string, extendedTimeOut: string, onclick: null, showDuration: string, timeOut: string, hideEasing: string, positionClass: string, hideDuration: string, preventDuplicates: boolean, closeButton: boolean, showEasing: string, progressBar: boolean, hideMethod: string}}
 */
toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-center",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

/**
 * Add new row to table.
 */
$('#addNewRow').on('click', function () {
    let lastRow = $('table#urlsTable>tbody>tr:last');
    let lastUrl = lastRow.find('input[name=rowUrl]');

    toastr.clear();

    if (lastUrl.val() === '') {
        toastr.error('Please, insert URL!');
        lastUrl.focus();
    } else {
        let newRow = $("<tr>");

        let cols = '<td><label><input type="text" class="form-control" name="rowUrl"/></label></td>';
        cols += '<td><label><input type="text" class="form-control" name="rowTags"/></label></td>';
        cols += '<td><label><input type="text" class="form-control" name="rowDesc"/></label></td>';
        cols += '<td><input type="button" class="btn btn-md btn-danger" onclick="deleteRow(this);" value="Remove"></td>';

        newRow.append(cols);

        $("table.order-list").append(newRow);
    }
});

/**
 * Remove row.
 *
 * @param element
 */
function deleteRow (element) {
    $(element).closest("tr").remove();
}

/**
 * Add given urls in DB
 */
$('#shareURLs').on('click', function () {
    let packs = [];
    let validate = true;

    toastr.clear();

    //Get all urls from table
    $('table#urlsTable>tbody>tr').each(function () {
        let URL = $(this).find('input[name=rowUrl]');
        let URLTags = $(this).find('input[name=rowTags]');
        let URLDesc = $(this).find('input[name=rowDesc]');

        let re = /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/gm;

        //Validate URL
        if (!re.test(String(URL.val()).toLowerCase())) {
            toastr.error('Invalid URL Format!');
            URL.focus();
            validate = false;
            return;
        } else {
            let pack = {
                'url': URL.val(),
                'tags': URLTags.val(),
                'desc': URLDesc.val()
            };

            //Add URL to URLs Array
            packs.push(pack);
        }
    });

    //Check if all URL area not empty
    if (validate) {
        let description = $('textarea#shareURLsDesc').val();
        let form = $('form[name=shareURL]');
        let token = $('input[name=shareToken]').val();
        let btn = $('#shareURLs');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: {token, description, packs},
            cache: false,
            success: function (response) {
                response = JSON.parse(response);

                if (response.status === 'created') {
                    let sharedLink = $('input#sharedLink');
                    let sharedLinkVal = sharedLink.val().replace('packLinkID', response.additions);
                    sharedLink.val(sharedLinkVal);

                    $('#packCreatedModal').modal();
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
 * Copy link to clipboard.
 */
$('#copySharedLink').on('click', function () {
    $('input#sharedLink').select();
    document.execCommand("copy");
    toastr.info('URL copied to clipboard.');
});

/**
 * Open link location.
 */
$('#openSharedLink').on('click', function () {
    window.location.href = $('input#sharedLink').val();
});