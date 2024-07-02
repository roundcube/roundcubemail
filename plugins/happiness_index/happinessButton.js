//Interactive Happiness Button in javascript.
function addHappinessButton() {
    const select = document.createElement("select")
    let options = [
        {text: "Happiness Index", value: "0"},
        {text: "Very Happy", value: "1"},
        {text: "Happy", value: "2"},
        {text: "Not so Happy", value: "3"},
        {text: "Sad", value: "4"},
        {text: "Very Sad", value: "5"}
    ];

    options.forEach(function(optionData) {
        let option = document.createElement("option");
        option.text = optionData.text;
        option.value = optionData.value;
        select.add(option);
    });

    select.addEventListener("change", function () {
       const selectedValue = select.value;
       getSelectedValue(selectedValue);
    });

    const container= document.getElementById("compose-toolbar")
    container.appendChild(select)
}

function getSelectedValue(selectedValue) {
   rcmail.http_post({
       url: 'happiness_index.php',
       data: {selectedValue: selectedValue},
       success: function (response) {
           console.log(response);
       }
   })
}
