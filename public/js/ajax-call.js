function MeGusta(id){
  var Ruta =' http://localhost:8000/like'
    $.ajax({
      type: 'POST',
      url: Ruta,
      data: ({id: id}),
      async: true,
      datatype: "json",
      success: function (data){
       console.log(data['like']);
        window.location.reload();
    }
  });
}