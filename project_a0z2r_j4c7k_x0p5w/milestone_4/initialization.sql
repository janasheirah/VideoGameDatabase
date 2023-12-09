/* drop table script so we can rerun multiple times*/

/* 1*/
drop table PLAYER cascade constraints;
drop table CLASSIFICATION cascade constraints;
drop table COMPANYADDRESSINFO cascade constraints;
drop table GAMEEDITIONHAVE cascade constraints;
drop table GAMESERIESMADEBY cascade constraints;
drop table COMPANY cascade constraints;

/* 2*/
drop table GAMESERIESPLAYERS cascade constraints;
drop table GAMESERIESWEBSITE cascade constraints;
drop table GENRES cascade constraints;
drop table PLATFORM cascade constraints;
drop table PLAY cascade constraints;
drop table PLAYEDON cascade constraints;


/* 3*/
drop table PLAYSON cascade constraints;
drop table PROFESSIONALPLAYER cascade constraints;
drop table PROFESSIONALPLAYERRANK cascade constraints;
drop table REVIEWEVALUATES cascade constraints;
drop table REVIEWEVALUATESRATING cascade constraints;
drop table TOURNAMENTWASABOUT cascade constraints;


/*Create tables Statements*/
CREATE TABLE Player (
PlayerID INTEGER PRIMARY KEY,
Name VARCHAR(20)
);

CREATE TABLE ProfessionalPlayerRank (
Rank VARCHAR(20) PRIMARY KEY,
Benefits INTEGER
);

CREATE TABLE ProfessionalPlayer (
PlayerID INTEGER PRIMARY KEY,
Rank VARCHAR(20),
FOREIGN KEY (PlayerID) REFERENCES Player(playerID),
FOREIGN KEY (Rank) REFERENCES ProfessionalPlayerRank(Rank) ON DELETE CASCADE
);

CREATE TABLE Platform (
Type VARCHAR(20) PRIMARY KEY,
Company VARCHAR(20)
);

CREATE TABLE CompanyAddressInfo (
PostalCode CHAR(6) PRIMARY KEY,
City VARCHAR(30),
Province VARCHAR(30)
);

CREATE TABLE Company (
PostalCode CHAR(6),
OfficeNum INTEGER,
Street VARCHAR(20),
Name VARCHAR(30),
CEO VARCHAR(20),
PRIMARY KEY(PostalCode, OfficeNum, Street),
FOREIGN KEY (PostalCode) REFERENCES CompanyAddressInfo(PostalCode)
);

CREATE TABLE Genres (
Type VARCHAR(20) PRIMARY KEY
);

CREATE TABLE GameSeriesPlayers (
PlayersNum INTEGER PRIMARY KEY,
Popularity VARCHAR(20)
);

CREATE TABLE GameSeriesWebsite (
GameWebsite VARCHAR(20) PRIMARY KEY,
GameName VARCHAR(20)
);

CREATE TABLE GameSeriesMadeBy (
GameID INTEGER PRIMARY KEY,
OfficeNum INTEGER NOT NULL,
Street VARCHAR(20) NOT NULL,
PostalCode CHAR(6) NOT NULL,
PlayersNum INTEGER,
GameWebsite VARCHAR(20),
FOREIGN KEY (PlayersNum) REFERENCES GameSeriesPlayers(PlayersNum),
FOREIGN KEY (GameWebsite) REFERENCES GameSeriesWebsite(GameWebsite),
FOREIGN KEY (PostalCode, OfficeNum, Street) REFERENCES Company(PostalCode, OfficeNum,
Street)
);

CREATE TABLE TournamentWasAbout (
TournamentName VARCHAR(20),
TournamentDate DATE,
GameID INTEGER NOT NULL,
Winner VARCHAR(20),
Prize VARCHAR(20),
ParticipantsNum INTEGER,
PRIMARY KEY(TournamentName, TournamentDate),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID)
);

CREATE TABLE PlayedOn (
TournamentName VARCHAR(20),
TournamentDate DATE,
PlayerID INTEGER NOT NULL,
PRIMARY KEY(TournamentName, TournamentDate, PlayerID),
FOREIGN KEY (TournamentName, TournamentDate) REFERENCES
TournamentWasAbout(TournamentName, TournamentDate),
FOREIGN KEY (PlayerID) REFERENCES Player(PlayerID)
);

CREATE TABLE Classification (
GenreType VARCHAR(20),
GameID INTEGER,
PRIMARY KEY(GenreType, GameID),
FOREIGN KEY (GenreType) REFERENCES Genres(Type),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID)
);

CREATE TABLE PlaysOn (
PlatformType VARCHAR(20),
GameID INTEGER,
PRIMARY KEY(PlatformType, GameID),
FOREIGN KEY (PlatformType) REFERENCES Platform(Type),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID)
);

CREATE TABLE Play (
PlayerID INTEGER,
GameID INTEGER,
PRIMARY KEY(PlayerID, GameID),
FOREIGN KEY(PlayerID) REFERENCES Player(PlayerID),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID)
);

CREATE TABLE GameEditionHave (
GameID INTEGER,
Edition VARCHAR(20),
ReleaseDate DATE,
PRIMARY KEY(GameID, Edition, ReleaseDate),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID) ON DELETE CASCADE
);

CREATE TABLE ReviewEvaluatesRating (
RatingScore INTEGER PRIMARY KEY,
RatingCateogry VARCHAR(20)
);

CREATE TABLE ReviewEvaluates (
ReviewID INTEGER PRIMARY KEY,
PlayerID INTEGER NOT NULL,
GameID INTEGER NOT NULL,
RatingScore INTEGER,
FOREIGN KEY (PlayerID) REFERENCES Player(PlayerID),
FOREIGN KEY (GameID) REFERENCES GameSeriesMadeBy(GameID),
FOREIGN KEY (RatingScore) REFERENCES ReviewEvaluatesRating(RatingScore)
);



/*Populate tables Statements*/

INSERT INTO Player(playerID, name) VALUES (1, 'Yahya');
INSERT INTO Player(playerID, name) VALUES (2, 'Jana');
INSERT INTO Player(playerID, name) VALUES (3, 'Andrew');
INSERT INTO Player(playerID, name) VALUES (4, 'Jessica');
INSERT INTO Player(playerID, name) VALUES (5, 'Sam');

INSERT INTO ProfessionalPlayerRank(Rank, Benefits) VALUES ('Bronze',3);
INSERT INTO ProfessionalPlayerRank(Rank, Benefits) VALUES ('Silver', 3);
INSERT INTO ProfessionalPlayerRank(Rank, Benefits) VALUES ('Gold', 2);
INSERT INTO ProfessionalPlayerRank(Rank, Benefits) VALUES ('Platinum', 2);
INSERT INTO ProfessionalPlayerRank(Rank, Benefits) VALUES ('Diamond', 1);
INSERT INTO ProfessionalPlayer(PlayerID, Rank) VALUES (1, 'Bronze');
INSERT INTO ProfessionalPlayer(PlayerID, Rank) VALUES (2, 'Silver');
INSERT INTO ProfessionalPlayer(PlayerID, Rank) VALUES (3, 'Gold');
INSERT INTO ProfessionalPlayer(PlayerID, Rank) VALUES (4, 'Platinum');
INSERT INTO ProfessionalPlayer(PlayerID, Rank) VALUES (5, 'Diamond');

INSERT INTO Platform(Type, Company) VALUES ('XBOX', 'Microsoft');
INSERT INTO Platform(Type, Company) VALUES ('Playstation 4', 'Sony');
INSERT INTO Platform(Type, Company) VALUES ('M18 Gaming Laptop', 'Alienware');
INSERT INTO Platform(Type, Company) VALUES ('G16 Gaming Laptop', 'DELL');
INSERT INTO Platform(Type, Company) VALUES ('Victus Gaming Laptop', 'HP');

INSERT INTO CompanyAddressInfo ( PostalCode, City, Province) VALUES ('K1A0A1', 'Ottawa', 'Ontario');
INSERT INTO CompanyAddressInfo ( PostalCode, City, Province) VALUES ('M5V2H1', 'Toronto', 'Ontario');
INSERT INTO CompanyAddressInfo ( PostalCode, City, Province) VALUES ('H3B4G7', 'Montreal', 'Quebec');
INSERT INTO CompanyAddressInfo ( PostalCode, City, Province) VALUES ('V6B4M9', 'Vancouver', 'British Columbia');
INSERT INTO CompanyAddressInfo ( PostalCode, City, Province) VALUES ('R3C0K6', 'Winnipeg', 'Manitoba');

INSERT INTO Company (PostalCode, OfficeNum, Street, Name, CEO) VALUES ('K1A0A1', 111, 'Main Street', 'ABC Inc', 'John Doe');
INSERT INTO Company (PostalCode, OfficeNum, Street, Name, CEO) VALUES ('M5V2H1', 234, 'Ross Street', 'Six Guys LLC', 'Ahmed Bin Sulaiman');
INSERT INTO Company (PostalCode, OfficeNum, Street, Name, CEO) VALUES ('H3B4G7', 244, 'Jordan Street', 'Jordan Inc', 'Jordan Jordan');
INSERT INTO Company (PostalCode, OfficeNum, Street, Name, CEO) VALUES ('V6B4M9', 455, 'Wall Street', 'New York', 'Jordan Belfort');
INSERT INTO Company (PostalCode, OfficeNum, Street, Name, CEO) VALUES ('R3C0K6', 678, 'Main Street', 'DEF Inc', 'Dohn Joe');

INSERT INTO Genres(Type) VALUES ('RPG');
INSERT INTO Genres(Type) VALUES ('MMO');
INSERT INTO Genres(Type) VALUES ('ACTION');
INSERT INTO Genres(Type) VALUES ('ROMANCE');
INSERT INTO Genres(Type) VALUES ('TERROR');

INSERT INTO GameSeriesPlayers(PlayersNum, Popularity) VALUES (10, 'Not Popular');
INSERT INTO GameSeriesPlayers(PlayersNum, Popularity) VALUES (50, 'Not Popular');
INSERT INTO GameSeriesPlayers(PlayersNum, Popularity) VALUES (200, 'Moderate Popular');
INSERT INTO GameSeriesPlayers(PlayersNum, Popularity) VALUES (1000, 'Popular');
INSERT INTO GameSeriesPlayers(PlayersNum, Popularity) VALUES (5000, 'Too Popular');

INSERT INTO GameSeriesWebsite(GameWebsite, GameName) VALUES ('Activision', 'Call of Duty');
INSERT INTO GameSeriesWebsite(GameWebsite, GameName) VALUES ('EASports.com','FIFA');
INSERT INTO GameSeriesWebsite(GameWebsite, GameName) VALUES ('Steam.com', 'FIFA');
INSERT INTO GameSeriesWebsite(GameWebsite, GameName) VALUES('PlaystationStore.com', 'Batman');
INSERT INTO GameSeriesWebsite(GameWebsite, GameName) VALUES ('Nintendo.com', 'Mario Kart');

INSERT INTO GameSeriesMadeBy (GameID, OfficeNum, Street, PostalCode, PlayersNum, GameWebsite) VALUES (12118088, 111, 'Main Street', 'K1A0A1', 10, 'Steam.com');
INSERT INTO GameSeriesMadeBy (GameID, OfficeNum, Street, PostalCode, PlayersNum, GameWebsite) VALUES (12446708, 234, 'Ross Street', 'M5V2H1', 50, 'EASports.com');
INSERT INTO GameSeriesMadeBy (GameID, OfficeNum, Street, PostalCode, PlayersNum, GameWebsite) VALUES (1255546, 244, 'Jordan Street', 'H3B4G7', 1000, 'Nintendo.com');
INSERT INTO GameSeriesMadeBy (GameID, OfficeNum, Street, PostalCode, PlayersNum, GameWebsite) VALUES (26419909, 455, 'Wall Street', 'V6B4M9', 1000, 'EASports.com');
INSERT INTO GameSeriesMadeBy (GameID, OfficeNum, Street, PostalCode, PlayersNum, GameWebsite) VALUES (7887676, 678, 'Main Street', 'R3C0K6', 200, 'Activision');

INSERT INTO TournamentWasAbout (TournamentName, TournamentDate, GameID, Winner, Prize, ParticipantsNum) VALUES ('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 12118088, 'Andrew', '$10,000', 100);
INSERT INTO TournamentWasAbout (TournamentName, TournamentDate, GameID, Winner, Prize, ParticipantsNum) VALUES ('Tournament B', TO_DATE('2023-10-21','YYYY-MM-DD'), 12446708, 'Andrew', '$9,000', 75);
INSERT INTO TournamentWasAbout (TournamentName, TournamentDate, GameID, Winner, Prize, ParticipantsNum) VALUES ('Tournament C', TO_DATE('2023-10-22','YYYY-MM-DD'), 1255546, 'Jessica', '$7,500', 50);
INSERT INTO TournamentWasAbout (TournamentName, TournamentDate, GameID, Winner, Prize, ParticipantsNum) VALUES ('Tournament D', TO_DATE('2023-10-23','YYYY-MM-DD'), 26419909, 'Jessica', '$2,500', 40);
INSERT INTO TournamentWasAbout (TournamentName, TournamentDate, GameID, Winner, Prize, ParticipantsNum) VALUES ('Tournament E', TO_DATE('2023-10-24','YYYY-MM-DD'),7887676, 'Jana', '$0', 30);

INSERT INTO PlayedOn (TournamentName, TournamentDate, PlayerID) VALUES('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 1);
INSERT INTO PlayedOn (TournamentName, TournamentDate, PlayerID) VALUES('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 2);
INSERT INTO PlayedOn (TournamentName, TournamentDate, PlayerID) VALUES('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 3);
INSERT INTO PlayedOn (TournamentName, TournamentDate, PlayerID) VALUES('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 4);
INSERT INTO PlayedOn (TournamentName, TournamentDate, PlayerID) VALUES('Tournament A', TO_DATE('2023-10-20','YYYY-MM-DD'), 5);
INSERT INTO PlayedOn VALUES('Tournament B', TO_DATE('2023-10-21', 'YYYY-MM-DD'), 1);
INSERT INTO PlayedOn VALUES('Tournament C', TO_DATE('2023-10-22', 'YYYY-MM-DD'), 1);
INSERT INTO PlayedOn VALUES('Tournament D', TO_DATE('2023-10-23', 'YYYY-MM-DD'), 1);
INSERT INTO PlayedOn VALUES('Tournament E', TO_DATE('2023-10-24', 'YYYY-MM-DD'), 1);

INSERT INTO Classification (GenreType, GameID) VALUES ('ACTION',12118088);
INSERT INTO Classification (GenreType, GameID) VALUES ('ROMANCE', 12446708 );
INSERT INTO Classification (GenreType, GameID) VALUES ('ACTION', 1255546);
INSERT INTO Classification (GenreType, GameID) VALUES ('RPG', 26419909);
INSERT INTO Classification (GenreType, GameID) VALUES ('ACTION', 7887676);

INSERT INTO PlaysOn (PlatformType, GameID) VALUES ('XBOX', 12118088);
INSERT INTO PlaysOn (PlatformType, GameID) VALUES ('Playstation 4', 12446708);
INSERT INTO PlaysOn (PlatformType, GameID) VALUES ('M18 Gaming Laptop', 1255546);
INSERT INTO PlaysOn (PlatformType, GameID) VALUES ('G16 Gaming Laptop', 26419909);
INSERT INTO PlaysOn (PlatformType, GameID) VALUES ('Victus Gaming Laptop', 7887676);

INSERT INTO Play (PlayerID, GameID) VALUES (1, 12118088);
INSERT INTO Play (PlayerID, GameID) VALUES (2, 12118088);
INSERT INTO Play (PlayerID, GameID) VALUES (3, 12446708);
INSERT INTO Play (PlayerID, GameID) VALUES (4, 26419909);
INSERT INTO Play (PlayerID, GameID) VALUES (5, 7887676);

INSERT INTO GameEditionHave (GameID, Edition, ReleaseDate) VALUES (12118088, 'Black OPS III', TO_DATE('2016-10-25','YYYY-MM-DD'));
INSERT INTO GameEditionHave (GameID, Edition, ReleaseDate) VALUES (12446708, 'Black OPS IIII', TO_DATE('2020-11-25','YYYY-MM-DD'));
INSERT INTO GameEditionHave (GameID, Edition, ReleaseDate) VALUES (1255546, 'Black OPS II', TO_DATE('2009-05-25','YYYY-MM-DD'));
INSERT INTO GameEditionHave (GameID, Edition, ReleaseDate) VALUES (26419909, 'Black OPS I', TO_DATE('2016-12-25','YYYY-MM-DD'));
INSERT INTO GameEditionHave (GameID, Edition, ReleaseDate) VALUES (7887676, 'Modern Warfare', TO_DATE('2008-9-25','YYYY-MM-DD'));

INSERT INTO ReviewEvaluatesRating (RatingScore, RatingCateogry) VALUES (1, 'Positive');
INSERT INTO ReviewEvaluatesRating (RatingScore, RatingCateogry) VALUES (2, 'Positive');
INSERT INTO ReviewEvaluatesRating (RatingScore, RatingCateogry) VALUES (3, 'Positive');
INSERT INTO ReviewEvaluatesRating (RatingScore, RatingCateogry) VALUES (4, 'Negative');
INSERT INTO ReviewEvaluatesRating (RatingScore, RatingCateogry) VALUES (5, 'Negative');

INSERT INTO ReviewEvaluates (ReviewID, PlayerID, GameID, RatingScore) VALUES (1, 1, 12118088, 1);
INSERT INTO ReviewEvaluates (ReviewID, PlayerID, GameID, RatingScore) VALUES (2, 2, 12446708, 2);
INSERT INTO ReviewEvaluates (ReviewID, PlayerID, GameID, RatingScore) VALUES (3, 3, 12446708, 3);
INSERT INTO ReviewEvaluates (ReviewID, PlayerID, GameID, RatingScore) VALUES (4, 4, 1255546, 4);
INSERT INTO ReviewEvaluates (ReviewID, PlayerID, GameID, RatingScore) VALUES (5, 5, 26419909, 5);
