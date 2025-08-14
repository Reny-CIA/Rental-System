function checkPasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    let strengthText = "";
    let color = "";

    switch(strength) {
        case 0:
        case 1:
            strengthText = "Very Weak";
            color = "red";
            break;
        case 2:
            strengthText = "Weak";
            color = "orange";
            break;
        case 3:
            strengthText = "Medium";
            color = "gold";
            break;
        case 4:
            strengthText = "Strong";
            color = "green";
            break;
        case 5:
            strengthText = "Very Strong";
            color = "darkgreen";
            break;
    }

    return {text: strengthText, color: color, score: strength};
}
