/** SAMUEL ADELOWOKAN - 
  */
$(function () {
    // Check stored box message and display it
    const storedMessage = localStorage.getItem("campaignMessage");
    if (storedMessage) {
        $('.box-message').html(storedMessage); // show box message
        localStorage.removeItem("campaignMessage"); // Clear it after showing
    }

    $("form:not('.ajax_off')").submit(async function (e) {
        e.preventDefault();

        var csv = $('#csvFileDropdown').val();
        var account = $('.account').val();
        var subject = $('.subject').val();
        var template = $('#txtFileDropdown').val();
        var days = $('.days:checked').map(function () {
            return this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();
        }).get().join(',');
        var time = $('.time').val();
        var id = $('form').attr('data-edit-id') ?? null;

        if (isEmpty(csv) || isEmpty(account) || isEmpty(template) || isEmpty(subject) || isEmpty(days) || isEmpty(time)) {
            alert('The Account, Csv, Message, Days and Time fields are required');
            return;
        }

        var load = $('.ajax_load');
        var btn = $('.ajax_btn');
        var boxMessage = $('.box-message');
        // var j_quantity_box = $('.j_quantity_box');
        // var j_quantity = $('.j_quantity');

        boxMessage.empty();

        load.fadeIn(200).css('display', 'flex');
        btn.fadeIn(200).css('display', 'none');

        // j_quantity_box.fadeIn(200).css('display', 'none');
        // j_quantity_box.fadeIn(200).css('display', 'flex');

        // let quantity = mobile.length;
        // j_quantity.text(quantity);

        let result;

        if (id) {
            result = await updateCampaign(account, days, time, subject, csv, template, id);
        } else {
            result = await saveCampaign(account, days, time, subject, csv, template);
        }

        load.fadeIn(200).css('display', 'none');
        btn.fadeIn(200).css('display', 'flex');

        // store box message to be displayed after page reload
        if (result && result.success) {
            localStorage.setItem("campaignMessage", 'Saving Campaign: - <span style="color:green">SUCCESS!</span>');
        }
        if (result && !result.success) {
            localStorage.setItem("campaignMessage", result.message + '<br> <span style="color:red">FAILED!</span>');
        }

        location.reload(); // reload page to refresh campaigns list
    });

    // Function to save campaign into database
    async function saveCampaign(account, days, time, subject, csv, template) {
        try {
            const result = await postData("service/savecampaign.php", { account, days, time, subject, csv, template });
            return result;
        } catch (error) {
            return false;
        }

    }

    // Function to update campaign into database */
    async function updateCampaign(account, days, time, subject, csv, template, id) {
        try {
            const result = await postData("service/updatecampaign.php", { account, days, time, subject, csv, template, id });
            return result;
        } catch (error) {
            return false;
        }

    }

    /** SAMUEL ADELOWOKAN
     * End Code
     */


    async function sendEmailUser(account, csv, subject, message) {
        try {
            const result = await postData("service/sendemail.php", { account, csv, subject, message });
            return result;
        } catch (error) {
            return false;
        }

    }

    async function postData(url = "", data = {}) {
        try {
            const response = await $.post(url, data);
            // return JSON.parse(response);
            return response;
        } catch (error) {
            console.error("AJAX or JSON error:", error);
            return { success: false, message: "AJAX or JSON error" };
        }
    }


    function isEmpty(value) {
        if (value === null || value === undefined) return true;
        return Object.keys(value).length === 0 || value === null
    }

});