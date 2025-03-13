document.addEventListener('DOMContentLoaded', function() {
    const competitions = [];

    function addCompetition(competition) {
        competitions.push(competition);
        competitions.sort((a, b) => {
            const timeA = a.time.replace(':', '');
            const timeB = b.time.replace(':', '');
            return timeA - timeB;
        });
        renderSchedule();
    }

    function renderSchedule() {
        const scheduleBody = document.getElementById('scheduleBody');
        scheduleBody.innerHTML = '';
        
        competitions.forEach(comp => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${comp.time}</td>
                <td>${comp.discipline}</td>
                <td>${comp.category}</td>
                <td>${comp.classValue}</td>
                <td>${comp.type}</td>
                <td>${comp.round}</td>
            `;
            scheduleBody.appendChild(row);
        });
    }

    document.getElementById('competitionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const competition = {
            time: document.getElementById('time').value,
            discipline: document.getElementById('discipline').value,
            category: document.getElementById('category').value,
            classValue: document.getElementById('class').value,
            type: document.getElementById('type').value,
            round: document.getElementById('round').value
        };

        addCompetition(competition);
        this.reset();
        document.getElementById('round').value = '1° Turno Finale';
    });
});