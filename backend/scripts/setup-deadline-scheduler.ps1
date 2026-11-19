# setup-deadline-scheduler.ps1
# Run this script once as Administrator to register the daily deadline reminder task
# in Windows Task Scheduler.

$PhpPath    = "php"           # Change if php.exe is not on PATH (e.g. C:\php\php.exe)
$BackendDir = Split-Path -Parent $PSScriptRoot  # Points to backend/ directory
$ConsolePath = "$BackendDir\bin\console"

$Action = New-ScheduledTaskAction `
    -Execute $PhpPath `
    -Argument "$ConsolePath app:deadline-reminder" `
    -WorkingDirectory $BackendDir

$Trigger = New-ScheduledTaskTrigger -Daily -At "07:00AM"

$Settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 5) `
    -StartWhenAvailable

Register-ScheduledTask `
    -TaskName "AACCUP_DeadlineReminder" `
    -Description "Sends in-app deadline notifications for approaching and overdue accreditation cycles." `
    -Action $Action `
    -Trigger $Trigger `
    -Settings $Settings `
    -RunLevel Highest `
    -Force

Write-Host "Task 'AACCUP_DeadlineReminder' registered. It will run daily at 7:00 AM." -ForegroundColor Green
Write-Host "To run it immediately: Start-ScheduledTask -TaskName 'AACCUP_DeadlineReminder'" -ForegroundColor Cyan
