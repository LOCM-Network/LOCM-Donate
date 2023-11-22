--# !sqlite
--# { cards
--#  { init
CREATE TABLE IF NOT EXISTS cards (player TEXT, amount INTEGER, telco TEXT, time TEXT)
--#  }
--#  { insert 
--#     :player string
--#     :amount number
--#     :telco string
--#     :time string
INSERT INTO cards (player, amount, telco, time) VALUES (:player, :amount, :telco, :pin, :serial, :time)
--#   }
--#  { select
--#     :player string
SELECT * FROM cards WHERE player = :player
--#   }
--#   { select_top 
SELECT player, SUM(amount) AS total_amount
FROM cards
GROUP BY player
ORDER BY total_amount DESC LIMIT 10;
--#   }
--# }
