function formValidate() {
    var input = document.forms["METARForm"]["metarInput"].value;

    if (input == "") {
        printError("Name must be filled out");
        return false;
    }

    var inputArr = input.split(" ");

    if(inputArr[0][0] == 'K' || inputArr[0][0] == 'P' || inputArr[0][0] == 'C'){
        if(inputArr[1].charAt(inputArr[1].length-1) == 'Z') {
            if(inputArr.includes("RMK")){
                return true;
            } else {
                printError("METAR format is not correct - Check if you&apos;ve included remarks!");
                return false;   
            }
        } else {
            printError("METAR format is not correct.");
            return false;   
        }
    } else {
        printError("North American airports only.");
        return false;         
    }

}

function printError(message){
    var alertBox = document.getElementById("METARAlert");
    if(alertBox.classList.contains("d-none")){
        alertBox.classList.remove("d-none");
        alertBox.classList.add("d-block");
    }
    alertBox.innerHTML = message;
}