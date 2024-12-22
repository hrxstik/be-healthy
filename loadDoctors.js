export function loadDoctors() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_doctors.php', true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('doctorsTable').innerHTML = this.responseText;
        } else {
            document.getElementById('doctorsTable').innerHTML = 'Ошибка получения информации о докторах';
        }
    };
    xhr.send();
}