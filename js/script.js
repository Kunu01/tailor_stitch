document.addEventListener("DOMContentLoaded", function() {
    const animatedWord = document.getElementById("animated-word");
    
   
    const words = ["Stitch", "Sewing", "Ready"];
    let index = 0;

    function changeWord() {
        animatedWord.textContent = words[index];
        index = (index + 1) % words.length;
    }

    setInterval(changeWord, 1200);
    
    setTimeout(function() {
        window.location.href = "login.php";
    }, 5000);


});
