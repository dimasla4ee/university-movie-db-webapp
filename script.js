let actorSuggestions = [];
let actorCounter = 1;
let factCounter = 1;

function addRow(type) {
    const container = document.getElementById(type === 'actor' ? 'actorsContainer' : 'factsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row';
    newRow.setAttribute('data-type', type);
    newRow.setAttribute('data-index', type === 'actor' ? actorCounter++ : factCounter++);

    if (type === 'actor') {
        newRow.innerHTML = `
            <input type="text" class="actor-input" name="actors[]" placeholder="Введите имя актёра" autocomplete="off">
            <button type="button" class="remove-btn" onclick="removeRow(this)">✖</button>
            <div class="suggestions"></div>
        `;
        container.appendChild(newRow);
        setupAutocomplete(newRow.querySelector('.actor-input'));
    } else {
        newRow.innerHTML = `
            <input type="text" class="fact-input" name="fun_facts[]" placeholder="Введите интересный факт">
            <button type="button" class="remove-btn" onclick="removeRow(this)">✖</button>
        `;
        container.appendChild(newRow);
    }
}

function removeRow(button) {
    const row = button.parentElement;
    const type = row.getAttribute('data-type');
    const container = document.getElementById(type === 'actor' ? 'actorsContainer' : 'factsContainer');
    if (container.children.length > 1) {
        row.remove();
    } else {
        alert('Должна быть хотя бы одна запись');
    }
}

function setupAutocomplete(input) {
    const suggestionsDiv = input.parentElement.querySelector('.suggestions');
    suggestionsDiv.hidden = true;

    input.addEventListener('input', () => {
        const value = input.value.toLowerCase();
        suggestionsDiv.innerHTML = '';

        if (value.length === 0) {
            suggestionsDiv.hidden = true;
            return;
        }

        const matches = actorSuggestions.filter(name => name.toLowerCase().includes(value));

        if (matches.length > 0) {
            matches.forEach(match => {
                const div = document.createElement('div');
                div.textContent = match;
                div.addEventListener('click', () => {
                    input.value = match;
                    suggestionsDiv.hidden = true;
                });
                suggestionsDiv.appendChild(div);
            });
            suggestionsDiv.hidden = false;
        } else {
            suggestionsDiv.hidden = true;
        }
    });

    input.addEventListener('blur', () => {
        setTimeout(() => {
            suggestionsDiv.hidden = true;
        }, 200);
    });
}

function addExistingActor() {
    const select = document.getElementById('existingActorSelect');
    const selectedName = select.value;

    if (selectedName === '') {
        alert('Выберите актёра из списка');
        return;
    }

    const inputs = document.querySelectorAll('.actor-input');
    if (Array.from(inputs).some(input => input.value === selectedName)) {
        alert('Этот актёр уже добавлен');
        return;
    }

    let targetInput = Array.from(inputs).find(input => input.value === '');
    if (!targetInput) {
        addRow('actor');
        targetInput = document.querySelector('#actorsContainer .row:last-child .actor-input');
    }
    targetInput.value = selectedName;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.actor-input').forEach(input => setupAutocomplete(input));
});