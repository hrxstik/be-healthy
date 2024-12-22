export function cancelAppointment(appointmentId) {
    if (confirm("Вы уверены, что хотите отменить запись?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "cancel_appointment.php?appointment_id=" + appointmentId, true);
        xhr.onload = function() {
            const messageDiv = document.getElementById("appointmentMessage");
            if (xhr.status === 200) {
                messageDiv.innerText = xhr.responseText;
            } else {
                messageDiv.innerText = "Ошибка: " + xhr.statusText;
            }
            console.log(xhr.responseText);
        };
        xhr.send();
    }
}