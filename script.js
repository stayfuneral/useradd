userData = {};
window.onload = function () { // get data from registration form
    let inputs = document.querySelectorAll('input[type=text], input[type=email], input[type=checkbox], select');
    inputs.forEach(function (item) {
        item.addEventListener('change', function () {
            switch (item.id) {
                case 'name':
                case 'lastName':
                case 'email':
                case 'position':
                case 'gender':
                case 'department':
                    userData[item.id] = item.value;;
                    break;
                case 'ufHead':
                    userData[item.id] = item.checked;
                    break;
            }
        });
    });
    let button = document.querySelector('button[type = submit]');
    button.addEventListener('click', function () {
        BX.ajax({
            url: 'ajax.php',
            method: 'POST',
            dataType: 'JSON',
            async: true,
            processData:true,
            data: userData,
            onsuccess: function(response) {
                let className, text;
                if(response.result === 'success') {
                    className = 'ui-alert ui-alert-success';
                    text = 'Пользователь успешно добавлен на портал';
                } else if(response.result === 'error') {
                    className = 'ui-alert ui-alert-danger';
                    text = response.result_message;
                }
                BX.append(BX.create('span', {
                    attrs: {
                       className: className 
                    },
                    html: text
                }), BX('result'));
                console.log(response);
            },
            onfailure: function(error) {
                console.log(error);
            }
        });
    });
}


