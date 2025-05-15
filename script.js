$(function () {
    $("form:not('.ajax_off')").submit(async function (e) {
        e.preventDefault();

        var allMobile = $('.mobile').val().trim();
        var account = $('.account').val();
        var subject = $('.subject').val();
        var msg = $('.msg').val();
        var days = $('.days:checked').map(function () {
            return this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();
        }).get().join(',');

        var time = $('.time').val();

        if (isEmpty(allMobile) || isEmpty(account) || isEmpty(msg) || isEmpty(subject) || isEmpty(days) || isEmpty(time)) {
            alert('The Account, Mobile, Message, Days and Time fields are required');
            return;
        }


        var mobile = allMobile.split("\n");
        var load = $('.ajax_load');
        var btn = $('.ajax_btn');
        var boxMessage = $('.box-message');
        var j_quantity_box = $('.j_quantity_box');
        var j_quantity = $('.j_quantity');

        boxMessage.empty();

        load.fadeIn(200).css('display', 'flex');
        btn.fadeIn(200).css('display', 'none');

        j_quantity_box.fadeIn(200).css('display', 'none');
        j_quantity_box.fadeIn(200).css('display', 'flex');

        let quantity = mobile.length;
        j_quantity.text(quantity);

        for (const [idx, data] of mobile.entries()) {
            const result = await sendEmailUser(account, data, subject, msg);
            showMessage(result, boxMessage);
            let newQuantity = quantity - (idx + 1);
            j_quantity.text(newQuantity);
        }

        load.fadeIn(200).css('display', 'none');
        btn.fadeIn(200).css('display', 'flex');


    });

    function showMessage(result, boxMessage) {
        if (result && result.success) {
            boxMessage.append('<p>Sending to: ' + result.email + ' - <span style="color:green">SUCCESS!</span></p>');
        }

        if (result && !result.success) {
            boxMessage.append('<p>Sending to: ' + result.email + ' - <span style="color:red">FAILED!</span></p>');
        }
    }


    async function sendEmailUser(account, mobile, subject, message) {

        try {
            const result = await postData("service/sendemail.php", { account, mobile, subject, message });
            return result;
        } catch (error) {
            return false;
        }

    }

    async function postData(url = "", data = {}) {
        const response = await $.post(url, data);
        return JSON.parse(response);
    }


    function isEmpty(value) {
        if (value === null || value === undefined) return true;
        return Object.keys(value).length === 0 || value === null
    }

});