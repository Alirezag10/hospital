(function () {
    'use strict';
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-password-toggle]');
        if (!button) return;
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', show ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
        button.setAttribute('title', show ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
    });

    const form = document.querySelector('[data-user-form]');
    if (!form) return;
    const role = form.querySelector('[data-user-role]');
    const doctorFields = form.querySelector('[data-user-doctor]');
    const doctor = form.querySelector('[data-user-doctor-id]');
    const newProfile = form.querySelector('[data-new-doctor-fields]');
    const name = form.querySelector('[data-user-full-name]');
    function updateDoctorFields() {
        const isDoctor = role.value === 'doctor';
        doctorFields.hidden = !isDoctor;
        doctorFields.querySelectorAll('input, select').forEach(input => { input.disabled = !isDoctor; });
        const existing = isDoctor && doctor.value !== '';
        newProfile.hidden = !isDoctor || existing;
        newProfile.querySelectorAll('input').forEach(input => { input.disabled = !isDoctor || existing; });
        name.readOnly = existing;
        const selectedName = doctor.selectedOptions?.[0]?.dataset.name;
        if (existing && selectedName) name.value = selectedName;
    }
    role.addEventListener('change', updateDoctorFields);
    doctor.addEventListener('change', updateDoctorFields);
    updateDoctorFields();
})();
