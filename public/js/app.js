let sesionGuardada = localStorage.getItem("userSession");
let csrfToken = $('meta[name="csrf-token"]').attr('content');

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': csrfToken
  }
});



//LO TRANSFORMAMOS A AJAX
$.ajax({
  url: '/login/license',
  type: 'POST',
  success: function (data) {
    if(data.data == null){
      document.getElementsByClassName('logoutClick')[0]?.click();
    }
  }
});

var intervalID = setInterval(myCallback, 7140000);
// console.log(intervalID);

function myCallback() {
  document.getElementsByClassName('logoutClick')[0]?.click();
}




