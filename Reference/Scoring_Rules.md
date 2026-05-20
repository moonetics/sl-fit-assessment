# Scoring Rules
# Community Fit Assessment

## 1. Output Utama
- Community Fit Score: 0–100
- Competitive Fit Score: 0–100
- Risk Level: Low, Medium, High
- Honesty Status: Valid, Questionable, Invalid
- Member Type:
  - Competitive Racer
  - Casual Community Member
  - Supportive Member
  - Quiet but Safe
  - Competitive but Risky
  - Drama-Prone Member
  - Rule-Resistant Member
  - Not Recommended
- Final Status:
  - Accepted
  - Accepted as Casual Member
  - Accepted with Trial
  - Manual Review
  - Watchlist
  - Retest
  - Rejected

## 2. Likert Scoring
Normal scoring:
```text
score = answer_value
```

Reverse scoring:
```text
score = 5 - answer_value
```

Convert category score:
```text
category_score_0_100 = ((raw_score - min_possible) / (max_possible - min_possible)) * 100
```

## 3. Situational Scoring
Default:
- Ideal = 4
- Acceptable = 3
- Risky = 1–2
- Red flag = 0

## 4. Community Fit Score Weights
| Kategori | Bobot |
|---|---:|
| Online Behavior | 12 |
| Toxicity Control | 14 |
| Sportsmanship | 10 |
| Respect for Casual Members | 10 |
| Conflict Handling | 12 |
| Rule Acceptance | 12 |
| Accountability | 10 |
| Drama Risk | 12 |
| Community Commitment | 8 |

Formula:
```text
community_fit = sum(category_score * weight) / 100
```

## 5. Competitive Fit Score Weights
| Kategori | Bobot |
|---|---:|
| Competitive Attitude | 45 |
| Sportsmanship | 25 |
| Accountability | 10 |
| Rule Acceptance | 10 |
| Respect for Casual Members | 10 |

Formula:
```text
competitive_fit = sum(category_score * weight) / 100
```

## 6. Risk Score
```text
risk_score =
(100 - ToxicityControlScore) * 0.25 +
(100 - ConflictHandlingScore) * 0.20 +
(100 - RuleAcceptanceScore) * 0.20 +
(100 - AccountabilityScore) * 0.15 +
(100 - DramaRiskScore) * 0.20
```

## 7. Risk Level
| Rule | Risk Level |
|---|---|
| risk_score < 35 and heavy_red_flags = 0 | Low |
| risk_score 35–64 or medium_red_flags >= 2 | Medium |
| risk_score >= 65 or heavy_red_flags >= 1 | High |

## 8. Honesty Status
| Rule | Honesty Status |
|---|---|
| contradiction_count <= 1 and suspicious_pattern low | Valid |
| contradiction_count 2–3 or extreme pattern medium | Questionable |
| contradiction_count >= 4 or straight-lining high or impossible perfection high | Invalid |

## 9. Suspicious Flags
Manual Review jika:
- total duration < configured_min_duration
- straight-lining >= 80% item skala
- answer perfection index terlalu tinggi
- contradiction_count >= 2
- refresh_count > threshold
- device_count > 2
- local/offline sync anomaly
- heavy red flag pada situational item

## 10. Final Status Rules
Order of precedence:
1. If honesty_status = Invalid and risk_level != High: Retest.
2. If honesty_status = Invalid and risk_level = High: Rejected.
3. If heavy_red_flags >= 2: Rejected.
4. If heavy_red_flags = 1 or risk_level = High: Watchlist.
5. If honesty_status = Questionable: Manual Review.
6. If competitive_fit >= 75 and risk_level != Low: Manual Review or Watchlist.
7. If community_fit >= 80 and risk_level = Low and honesty_status = Valid: Accepted.
8. If community_fit >= 70 and competitive_fit < 55 and risk_level != High: Accepted as Casual Member.
9. If community_fit >= 65 and risk_level != High: Accepted with Trial.
10. Else: Manual Review or Rejected based on admin policy.

## 11. Member Type Rules
| Condition | Member Type |
|---|---|
| community_fit >= 75, competitive_fit >= 75, risk_level = Low | Competitive Racer |
| community_fit >= 70, competitive_fit < 55, risk_level != High | Casual Community Member |
| respect >= 75, online_behavior >= 75, commitment >= 65 | Supportive Member |
| drama_risk >= 75, online_behavior >= 70, commitment 45–70, competitive_fit < 55 | Quiet but Safe |
| competitive_fit >= 75 and risk_level != Low | Competitive but Risky |
| drama_risk < 50 or conflict_handling < 50 | Drama-Prone Member |
| rule_acceptance < 50 | Rule-Resistant Member |
| risk_level = High and community_fit < 60 | Not Recommended |
