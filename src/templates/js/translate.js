function translateGoogle(id) {

    textFrom = document.getElementById(id + '_from').innerHTML;

    var url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl="
        + '{{ siteFrom.language[:2] }}' + "&tl=" + '{{ siteTo.language[:2] }}' + "&dt=t";

    $.post(url, {q: textFrom})
        .done(function(data) {

            console.log(data);

            translatedText = '';
            for (i = 0; i < data[0].length; i++) {
                translatedText += data[0][i][0];
            }
            // alert( "Translation done. Handle with care." );
            document.getElementById(id + '_to').value = translatedText.replace(/<br>/g, '');

        })
        .fail(function() {
            alert("error");
        });

}

