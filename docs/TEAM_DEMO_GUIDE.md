   # AACCUP PAMS Team Demo Guide

   Use this runbook when demonstrating the complete AACCUP PAMS workflow. The
   recommended order lets each teammate demonstrate a different role and shows
   how work moves from planning to review.

   ## Demo Goal

   By the end of the demo, the team should show how to:

   1. Sign in and view role-based information.
   2. Launch an accreditation cycle.
   3. Assign an Internal Accreditor.
   4. Create and submit a compliance activity.
   5. Upload supporting evidence.
   6. Review, approve, or return an activity.
   7. View notifications, the compliance schedule, and a generated report.
   8. Confirm that read-only users cannot change compliance records.

   ## Exact Step-by-Step Demo Process

   Follow these steps in order during the presentation. Use the same demo
   program and cycle throughout the process.

   ### Step 1: Prepare the demo

   1. Open the PAMS URL.
   2. Prepare one sample PDF or image for evidence.
   3. Decide which program and AACCUP area will be used.
   4. Keep the following accounts ready:
      - Admin: `admin@norsu.edu.ph`
      - Program Head: `ph.msit@norsu.edu.ph`
      - Internal Accreditor: `ia1@norsu.edu.ph`
      - Area Coordinator: `ac.gs@norsu.edu.ph`
      - President: `president@norsu.edu.ph`
   5. Use `Password123!` for seeded development accounts.

   ### Step 2: Log in as QUAMC Admin

   1. Enter `admin@norsu.edu.ph` in **Email address**.
   2. Enter the password and select **Sign in**.
   3. Confirm that **Dashboard** and **Administration** are visible.
   4. Open **Administration**.

   ### Step 3: Launch or select the demo cycle

   If a suitable test cycle already exists, skip to step 4.

   1. Under **Launch New Cycle**, select a program.
   2. Confirm that the program's current accreditation level is displayed.
   3. If the program is Level III, select the program type and exactly two optional areas.
   4. Enter the academic year.
   5. Enter the **SAR Received Date**.
   6. Select **Launch Cycle**.
   7. Confirm the success message.
   8. Confirm that the cycle appears in **All Cycles**.
   9. Explain that completing every area advances the program to the next level
      automatically for its next cycle.
   10. To enter findings from the SAR, open the cycle, expand an AACCUP area,
       and select **Add Recommendation** in the SAR Recommendations section.
       Enter the recommendation text, select Mandatory or Enhancement, and
       choose its priority.

   ### Step 4: Assign the Internal Accreditor

   1. Remain on **Administration**.
   2. Under **Assign Internal Accreditor**, select `ia1@norsu.edu.ph`.
   3. Select the demo program.
   4. Select **Assign Program**.
   5. Confirm that the assignment appears in the assignment list.
   6. Select **Sign Out**.

   ### Step 5: Log in as Program Head

   1. Enter `ph.msit@norsu.edu.ph`.
   2. Enter `Password123!`.
   3. Select **Sign in**.
   4. On **Dashboard**, select the demo cycle.
   5. Expand an AACCUP area.

   ### Step 6: Create an activity

   1. Select **New Activity**.
   2. Enter the title, for example **Review and update the faculty handbook**.
   3. Enter a short description.
   4. Select a start date.
   5. Select an end date that is on or before the cycle deadline.
   6. Select **Create Activity**.
   7. Confirm that the activity appears with the **Draft** state.

   ### Step 7: Upload activity evidence

   1. In the activity row, select **Upload**.
   2. Choose the prepared PDF or image.
   3. Check or edit the **Display Name**.
   4. Select **Upload Evidence**.
   5. Confirm the success message.
   6. Return to the activity edit page or cycle detail page.

   ### Step 8: Submit the activity

   1. Open the activity again.
   2. Confirm the title, description, dates, and evidence.
   3. Select **Submit for Review**.
   4. Confirm that the state changes to **Submitted**.
   5. Select **Sign Out**.

   ### Step 9: Review the activity as Internal Accreditor

   1. Enter `ia1@norsu.edu.ph`.
   2. Enter `Password123!`.
   3. Select **Sign in**.
   4. Open the same cycle and AACCUP area.
   5. Select **IA Review**.
   6. Confirm that the submitted activity appears under **Activities Pending
      Review**.
   7. Select **Start Review**.
   8. Select **Request Revision** to demonstrate the revision path.
   9. Enter a reason in the area comments.
   10. Select **Save IA Review**.
   11. Confirm the activity is marked **Needs Revision**.
   12. Select **Sign Out**.

   ### Step 10: Revise and resubmit as Program Head

   1. Sign in again as `ph.msit@norsu.edu.ph`.
   2. Open the same cycle, area, and activity.
   3. Select the activity title to edit it.
   4. Update the description or dates as requested by the IA.
   5. Select **Update Activity**.
   6. Select **Submit for Review**.
   7. Confirm that the state changes back to **Submitted**.
   8. Select **Sign Out**.

   ### Step 11: Approve the activity as Internal Accreditor

   1. Sign in as `ia1@norsu.edu.ph`.
   2. Open the same cycle, area, and **IA Review** page.
   3. Select **Start Review**.
   4. Select **Approve**.
   5. Set the area-level IA status to **Approved**.
   6. Enter a final review comment.
   7. Select **Save IA Review**.
   8. Confirm that the activity state is **Approved**.
   9. Select **Sign Out**.

   ### Step 12: Show the schedule and report

   1. Sign in as `ph.msit@norsu.edu.ph` or `admin@norsu.edu.ph`.
   2. Open the approved cycle.
   3. Select **Schedule**.
   4. Confirm that the approved activity appears in the Gantt chart.
   5. Return to the cycle detail page.
   6. Select **Generate Report**.
   7. Wait for the report to download.
   8. Open the downloaded report if the audience needs to see it.
   9. Select **Sign Out**.

   ### Step 13: Demonstrate area evidence

   1. Sign in as `ac.gs@norsu.edu.ph`.
   2. Open the assigned cycle and area.
   3. Select **Upload Evidence** at the area level.
   4. Choose a PDF or image.
   5. Select **Upload Evidence**.
   6. Confirm the success message.
   7. Select **Sign Out**.

   ### Step 14: Demonstrate read-only access

   1. Sign in as `president@norsu.edu.ph`.
   2. Open **Dashboard**.
   3. Open the same cycle.
   4. Expand an area.
   5. Show the cycle status, activity state, evidence, and IA comments.
   6. Explain that this account can monitor records but cannot create activities,
      upload evidence, launch cycles, or approve activities.
   7. Select **Sign Out**.

   ### Step 15: Close the presentation

   1. Confirm that the activity is **Approved**.
   2. Confirm that the evidence upload succeeded.
   3. Confirm that the schedule and report were demonstrated.
   4. Confirm that every presenter signed out.
   5. Remind the audience that the listed password is for development accounts
      only.

   ## Before the Demo

   - Start the frontend and backend, then open the local or deployed PAMS URL.
   - Prepare one demo account for each role. The seeded development accounts all
   use the password `Password123!`.
   - Use a test program or an existing demo cycle so the presentation does not
   change production accreditation records.
   - Prepare one small PDF or image as sample evidence.
   - Agree on one sample activity, such as **Review and update the faculty
   handbook**, with dates that fall before the cycle deadline.

   ### Seeded development accounts

   The following accounts are created by the development fixtures. The password
   for every account below is `Password123!`.

   > **Development only:** Never use these credentials in production, never send
   > them to external users, and change or remove seeded passwords before
   > deployment.

   #### Executive, oversight, and administration accounts

   | Role | Email |
   | --- | --- |
   | QUAMC Admin | `admin@norsu.edu.ph` |
   | President | `president@norsu.edu.ph` |
   | Campus Director | `director@norsu.edu.ph` |
   | VPAA | `vpaa@norsu.edu.ph` |

   #### Internal Accreditors

   | Role | Email |
   | --- | --- |
   | Internal Accreditor | `ia1@norsu.edu.ph` |
   | Internal Accreditor | `ia2@norsu.edu.ph` |
   | Internal Accreditor | `ia3@norsu.edu.ph` |

   #### Deans

   | College | Email |
   | --- | --- |
   | Graduate School | `dean.gs@norsu.edu.ph` |
   | College of Agriculture, Forestry and Fishery | `dean.caff@norsu.edu.ph` |
   | College of Arts and Sciences | `dean.cas@norsu.edu.ph` |
   | College of Business Administration | `dean.cba@norsu.edu.ph` |
   | College of Criminal Justice Education | `dean.ccje@norsu.edu.ph` |
   | College of Engineering and Architecture | `dean.cea@norsu.edu.ph` |
   | College of Industrial Technology | `dean.cit@norsu.edu.ph` |
   | College of Nursing, Pharmacy and Allied Health Sciences | `dean.cnpahs@norsu.edu.ph` |
   | College of Tourism and Hospitality Management | `dean.cthm@norsu.edu.ph` |
   | College of Teacher Education | `dean.cte@norsu.edu.ph` |

   #### Program Heads

   Use the program code in the table to form the email:
   `ph.<program-code>@norsu.edu.ph`.

   | College | Program-head emails |
   | --- | --- |
   | Graduate School | `ph.dedum@norsu.edu.ph`, `ph.dedscitech@norsu.edu.ph`, `ph.dmmgmt@norsu.edu.ph`, `ph.phdedmgt@norsu.edu.ph`, `ph.phdmath@norsu.edu.ph`, `ph.mph@norsu.edu.ph`, `ph.maece@norsu.edu.ph`, `ph.mte@norsu.edu.ph`, `ph.phdelt@norsu.edu.ph`, `ph.dedfil@norsu.edu.ph`, `ph.dedinstr@norsu.edu.ph`, `ph.msagri@norsu.edu.ph`, `ph.mapsych@norsu.edu.ph`, `ph.mape@norsu.edu.ph`, `ph.mba@norsu.edu.ph`, `ph.phdmath2@norsu.edu.ph`, `ph.msit@norsu.edu.ph`, `ph.ma@norsu.edu.ph`, `ph.msmath@norsu.edu.ph`, `ph.mamathteach@norsu.edu.ph`, `ph.maeng@norsu.edu.ph`, `ph.mpm@norsu.edu.ph`, `ph.mpa@norsu.edu.ph` |
   | CAFF | `ph.bsfish@norsu.edu.ph`, `ph.bsfor@norsu.edu.ph` |
   | CAS | `ph.bagc@norsu.edu.ph`, `ph.bsss@norsu.edu.ph`, `ph.bsbio@norsu.edu.ph`, `ph.bschem@norsu.edu.ph`, `ph.bsgeo@norsu.edu.ph`, `ph.bscs@norsu.edu.ph`, `ph.bsit@norsu.edu.ph`, `ph.bamc@norsu.edu.ph`, `ph.bsmath@norsu.edu.ph`, `ph.bspsych@norsu.edu.ph` |
   | CBA | `ph.bsacc@norsu.edu.ph`, `ph.bsba@norsu.edu.ph`, `ph.bsoa@norsu.edu.ph` |
   | CCJE | `ph.bscrim@norsu.edu.ph` |
   | CEA | `ph.bsarch@norsu.edu.ph`, `ph.bsce@norsu.edu.ph`, `ph.bscoe@norsu.edu.ph`, `ph.bsee@norsu.edu.ph`, `ph.bsece@norsu.edu.ph`, `ph.bsge@norsu.edu.ph`, `ph.bsgte@norsu.edu.ph`, `ph.bsme@norsu.edu.ph` |
   | CIT | `ph.bsit2@norsu.edu.ph`, `ph.bt@norsu.edu.ph` |
   | CNPAHS | `ph.bsn@norsu.edu.ph`, `ph.bspharma@norsu.edu.ph` |
   | CTHM | `ph.bshm@norsu.edu.ph`, `ph.bstm@norsu.edu.ph` |
   | CTE | `ph.beed@norsu.edu.ph`, `ph.bseda@norsu.edu.ph`, `ph.bsedb@norsu.edu.ph`, `ph.btle@norsu.edu.ph`, `ph.bted@norsu.edu.ph` |

   #### Area Coordinators

   | College | Email |
   | --- | --- |
   | Graduate School | `ac.gs@norsu.edu.ph` |
   | College of Agriculture, Forestry and Fishery | `ac.caff@norsu.edu.ph` |
   | College of Arts and Sciences | `ac.cas@norsu.edu.ph` |
   | College of Business Administration | `ac.cba@norsu.edu.ph` |
   | College of Criminal Justice Education | `ac.ccje@norsu.edu.ph` |
   | College of Engineering and Architecture | `ac.cea@norsu.edu.ph` |
   | College of Industrial Technology | `ac.cit@norsu.edu.ph` |
   | College of Nursing, Pharmacy and Allied Health Sciences | `ac.cnpahs@norsu.edu.ph` |
   | College of Tourism and Hospitality Management | `ac.cthm@norsu.edu.ph` |
   | College of Teacher Education | `ac.cte@norsu.edu.ph` |

   ## Recommended Presentation Order

   ### 1. Sign in and show the Dashboard

   **Presenter:** Any teammate, then repeat with the main demo roles.

   1. Open the PAMS URL.
   2. Enter the institutional email and password.
   3. Select **Sign in**.
   4. Point out the cycle cards, program, college, academic year, deadline, and
      status.
   5. Explain the urgency colors:
      - Green: more than 30 days remaining.
      - Amber: 8–30 days remaining.
      - Red: fewer than 8 days remaining or overdue.
   6. Select a cycle card to open its details.
   7. Select the bell icon to show notifications. Open a notification to follow
      its link, or use **Mark all read**.

   Sign out before switching to another account. This makes the role changes
   clear and prevents the next presenter from using the previous session.

   ### 2. Launch a cycle

   **Presenter:** QUAMC Admin.

   1. Sign in as the QUAMC Admin.
   2. Open **Administration** from the left navigation.
   3. In **Launch New Cycle**, select a program and confirm its current
      accreditation level.
   4. For Level III, select **Undergraduate** or **Graduate**, then choose two
      optional areas.
   5. Enter the academic year and **SAR Received Date**.
   6. Select **Launch Cycle**.
   7. Show the success message and the new entry in **All Cycles**.

   Explain that the system calculates the compliance deadline and generates the
   cycle's area assignments automatically. When every area is approved, the
   cycle is completed and the program advances one accreditation level for its
   next cycle, up to Level IV.

   ### 3. Assign the Internal Accreditor

   **Presenter:** QUAMC Admin.

   1. Stay on **Administration**.
   2. In **Assign Internal Accreditor**, select an Internal Accreditor.
   3. Select the same program used for the demo cycle.
   4. Select **Assign Program**.
   5. Show the assignment in the list below the form.

   Explain that the assigned IA reviews the program's areas and activities. Use
   **Remove** only if the assignment was created for testing and must be cleared.

   ### 4. Create a compliance activity

   **Presenter:** Program Head.

   1. Sign out, then sign in as the Program Head assigned to the demo program.
   2. Open the program's cycle from **Dashboard**.
   3. Expand an AACCUP area, preferably one with a relevant SAR
      recommendation.
   4. Select **New Activity**.
   5. Enter:
      - A clear title.
      - A short description.
      - A start date.
      - An end date on or before the displayed deadline.
   6. Select **Create Activity**.
   7. Reopen the activity from the activity list to show its **Draft** state.
   8. Select **Submit for Review**.

   Point out that activities cannot be scheduled beyond the cycle deadline.
   Program Heads can edit draft or needs-revision activities, but not activities
   already under review or approved.

   ### 5. Upload evidence

   **Presenter:** Program Head or Area Coordinator.

   1. From the cycle detail page, expand the relevant area.
   2. For activity evidence, select **Upload** beside the activity.
   3. For area-level evidence, select **Upload Evidence** in the area actions.
   4. Choose or drag in the prepared PDF/image.
   5. Optionally edit the **Display Name**.
   6. Select **Upload Evidence**.
   7. Show the success message.

   Use PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, JPG/JPEG, or PNG files no larger than
   20 MB. Explain that evidence is private and access is controlled by the
   related cycle and the signed-in user's permissions.

   ### 6. Review the submitted activity

   **Presenter:** Internal Accreditor.

   1. Sign out, then sign in as the IA assigned to the program.
   2. Open the same cycle and area.
   3. Select **IA Review**.
   4. In **Activities Pending Review**, select **Start Review**.
   5. Demonstrate one of these paths:
      - Select **Approve** to move the activity to **Approved**.
      - Select **Request Revision** to move it to **Needs Revision**.
   6. In **Area-Level Decision**, select an IA status:
      - **Approved**, or
      - **Needs Revision**.
   7. Enter a short comment and select **Save IA Review**.

   For the full workflow, first request a revision. Switch back to the Program
   Head, edit the activity, and select **Submit for Review** again. Return to the
   IA account and approve it.

   ### 7. Show the schedule and report

   **Presenter:** QUAMC Admin or Program Head.

   1. Open the cycle detail page.
   2. Select **Schedule**.
   3. Explain that approved and completed activities appear in the Gantt chart.
   4. Return to cycle details.
   5. Select **Generate Report**.
   6. Wait for the report download to finish, then open the downloaded file if
      requested by the audience.

   If the schedule is empty, confirm that at least one activity has reached
   **Approved** or **Completed**. Draft, submitted, and under-review activities
   do not appear in the schedule.

   ### 8. Demonstrate read-only access

   **Presenter:** President, Campus Director, VPAA, or Dean.

   1. Sign out, then sign in as the read-only role.
   2. Open the Dashboard and the same cycle.
   3. Expand areas and show that cycle progress, activity states, evidence
      availability, and IA comments can be viewed.
   4. Explain that read-only users do not create activities, upload evidence,
      launch cycles, or change review decisions.

   ## Activity State Storyboard

   Use this table while explaining the workflow:

   | State | Demonstrate it by |
   | --- | --- |
   | Draft | Create an activity as Program Head |
   | Submitted | Select **Submit for Review** |
   | Under Review | IA selects **Start Review** |
   | Needs Revision | IA selects **Request Revision** |
   | Approved | IA selects **Approve** |
   | Completed | Use the system's completion workflow or an existing completed demo activity |

   ## Role Cheat Sheet

   | Role | Main demo actions |
   | --- | --- |
   | QUAMC Admin | Launch cycles, assign IAs, view all records, generate reports |
   | Program Head | Create/edit activities, submit revisions, upload evidence, view schedule |
   | Internal Accreditor | Start reviews, approve/request revisions, save area decisions |
   | Area Coordinator | Upload area-level evidence and monitor assigned college areas |
   | Dean | Monitor programs and cycles for the assigned college |
   | President/Campus Director/VPAA | Institution-level read-only monitoring |

   ## Troubleshooting During the Demo

   - **Cycle is not visible:** confirm the signed-in account is assigned to the
   correct program, college, or area.
   - **Administration is missing:** the account is not a QUAMC Admin.
   - **New Activity is missing:** use the Program Head or Admin account.
   - **IA Review has no activities:** the activity must be submitted first, and
   the IA must be assigned to that program.
   - **Upload fails:** check the file type, file size, and account permissions.
   - **Schedule is empty:** approve an activity first.
   - **Page shows an old result:** refresh after switching accounts, or sign out
   completely and sign in again.

   ## Closing Checklist

   - [ ] Every presenter signed out after their role demonstration.
   - [ ] The sample activity has a final state agreed by the team.
   - [ ] Temporary evidence and test cycles were not created in production.
   - [ ] The report download was demonstrated or noted as unavailable.
   - [ ] The audience saw both successful actions and permission boundaries.

