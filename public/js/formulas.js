function displayBarcode() {
    var val = Date.now()
    const uniqueId = Math.random().toString(36).substr(2, 22);

    var nId = ""+uniqueId+val;
    var valT = nId.toString().substr(7,13);
   
    //window.alert(" Barcode : "+valT);

    const myElement = document.getElementById("barcode");
    myElement.value = valT;

}


    