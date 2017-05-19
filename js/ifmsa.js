function loading()
{
    $('#loading').css('display', 'inline-flex');
    $('#spin_loader').addClass('active');
}

function remove_loading()
{
    var load = document.getElementById('loading');
    if (load){ load.style.display = 'none'; }
}

function loading_removable()
{
    loading();
    var load = document.getElementById('loading');
    load.onclick = function(){ document.getElementById('loading').style.display = 'none'; }
}

function require_info()
{
    $('#require_info').css('display', 'inline-flex');
}

function check_passwd(pass1, pass2, submit)
{
    //Store the password field objects into variables ...
    var pass1 = document.getElementById(pass1);
    var pass2 = document.getElementById(pass2);
    //Submit button
    var submit_button = document.getElementById(submit);
    //Compare the values in the password field
    //and the confirmation field
    if(pass1.value == pass2.value){
        //The passwords match.
        //Set the color to the good color and inform
        //the user that they have entered the correct password
        if(pass1.classList.contains('invalid')){ pass1.classList.remove('invalid'); }
        if(pass2.classList.contains('invalid')){ pass2.classList.remove('invalid'); }
        pass1.classList.add('valid');
        pass2.classList.add('valid');

        submit_button.disabled = false;
    }else{
        //The passwords do not match.
        //Set the color to the bad color and
        //notify the user.
        if(pass1.classList.contains('valid')){ pass1.classList.remove('valid'); }
        if(pass2.classList.contains('valid')){ pass2.classList.remove('valid'); }
        pass1.classList.add('invalid');
        pass2.classList.add('invalid');

        submit_button.disabled = true;
    }
}