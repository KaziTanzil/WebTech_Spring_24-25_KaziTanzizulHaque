function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

function showBoard(boardId) {
    console.log(`Showing board: ${boardId}`);
    const boards = document.querySelectorAll('.board-content');
    boards.forEach(board => {
        board.style.display = board.id === boardId ? 'block' : 'none';
    });
    const buttons = document.querySelectorAll('.sidebar button');
    buttons.forEach(button => {
        button.classList.toggle('active', button.getAttribute('aria-controls') === boardId);
        button.setAttribute('aria-selected', button.getAttribute('aria-controls') === boardId);
    });
}

function editUser(userId) {
    console.log(`Editing user: ${userId}`);
    // Implement edit user logic
}

function deleteUser(userId) {
    if (confirm(`Are you sure you want to delete user ${userId}?`)) {
        console.log(`Deleting user: ${userId}`);
        // Implement delete user logic
    }
}

function editCourse(courseId) {
    console.log(`Editing course: ${courseId}`);
    // Implement edit course logic
}

function deleteCourse(courseId) {
    if (confirm(`Are you sure you want to delete course ${courseId}?`)) {
        console.log(`Deleting course: ${courseId}`);
        // Implement delete course logic
    }
}

function editQuiz(quizId) {
    console.log(`Editing quiz: ${quizId}`);
    // Implement edit quiz logic
}

function deleteQuiz(quizId) {
    if (confirm(`Are you sure you want to delete quiz ${quizId}?`)) {
        console.log(`Deleting quiz: ${quizId}`);
        // Implement delete quiz logic
    }
}

function editScore(scoreId, userId, userName, score, quizName, courseId) {
    console.log(`Editing score: ${scoreId}`);
    document.getElementById('edit_score_id').value = scoreId;
    document.getElementById('edit_score').value = score;
    document.getElementById('edit_quiz_name').value = quizName;
    document.getElementById('edit_course_id').value = courseId;
    document.getElementById('edit-score-form').style.display = 'block';
}

function cancelEdit() {
    document.getElementById('edit-score-form').style.display = 'none';
}

document.getElementById('score_search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#scores_table tbody tr');
    rows.forEach(row => {
        const uid = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const quizName = row.cells[3].textContent.toLowerCase();
        const courseName = row.cells[4].textContent.toLowerCase();
        row.style.display = (uid.includes(searchTerm) || name.includes(searchTerm) || 
                            quizName.includes(searchTerm) || courseName.includes(searchTerm)) ? '' : 'none';
    });
});