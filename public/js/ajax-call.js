function MeGusta(id){
  var Ruta = Routing.generate('like');
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