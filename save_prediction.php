<?php
include 'connection.php';

if (isset($_POST['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['id']);

    $query = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$student_id'");
    $student = mysqli_fetch_assoc($query);

    if ($student) {
        $data = [
            "weekly_self_study_hours" => (float)$student['weekly_self_study_hours'],
            "attendance_percentage"   => (float)$student['attendance_percentage'],
            "class_participation"     => (float)$student['class_participation'],
            "total_score"             => (float)$student['total_score']
        ];

        $ch = curl_init('http://127.0.0.1:5000/predict');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            $ai_grade = mysqli_real_escape_string($conn, $result['predicted_grade']);

            mysqli_query($conn, "UPDATE students SET grade = '$ai_grade' WHERE student_id = '$student_id'");

            $attendance = $student['attendance_percentage'];
            $hours = $student['weekly_self_study_hours'];
            $part = $student['class_participation'];
            $score = $student['total_score'];

            $save_prediction = "INSERT INTO predictions 
                (student_id, attendance, hours, participation, score, predicted_grade, status, prediction_date) 
                VALUES 
                ('$student_id', '$attendance', '$hours', '$part', '$score', '$ai_grade', 'Finalized', NOW())
                ON DUPLICATE KEY UPDATE 
                attendance = '$attendance',
                hours = '$hours',
                participation = '$part',
                score = '$score',
                predicted_grade = '$ai_grade',
                status = 'Finalized',
                prediction_date = NOW()";

            mysqli_query($conn, $save_prediction);

            echo "
            <div class='prediction-card animated-fade-in' style='text-align: center; padding: 20px; background: rgba(46, 204, 113, 0.1); border: 1px solid #2ecc71; border-radius: 15px;'>
                <p style='color: #888; margin-bottom: 10px;'>Analysis Complete ✨</p>
                <h2 style='font-size: 4rem; color: #2ecc71; margin: 0;'>$ai_grade</h2>
                <span style='color: #aaa; font-size: 0.9rem;'>InsightEd AI has successfully calculated the grade.</span>
            </div>
            <script>
                if(typeof lucide !== 'undefined') { lucide.createIcons(); }
            </script>";
        } else {
            echo "
            <div class='error-msg' style='display: flex; align-items: center; gap: 10px; color: #e74c3c; background: rgba(231, 76, 60, 0.1); padding: 15px; border-radius: 10px; border: 1px solid #e74c3c;'>
                <i data-lucide='alert-circle'></i>
                <span>AI Server is offline. Please start app.py in your VS Code terminal.</span>
            </div>
            <script>
                if(typeof lucide !== 'undefined') { lucide.createIcons(); }
            </script>";
        }
    }
}
?>